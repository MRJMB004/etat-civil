<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class CauseDeces extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'causes_deces';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'libelle', 
        'description',
        'categorie',
        'est_evitable',
        'gravite',
        'age_min_affecte',
        'age_max_affecte',
        'est_actif'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'est_evitable' => 'boolean',
        'est_actif' => 'boolean',
        'age_min_affecte' => 'integer',
        'age_max_affecte' => 'integer',
        'gravite' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($causeDeces) {
            // Générer un code unique si non fourni
            if (empty($causeDeces->code)) {
                $causeDeces->code = static::generateUniqueCode();
            }
        });

        // Empêcher la suppression si la cause est utilisée dans des décès
        static::deleting(function ($causeDeces) {
            if ($causeDeces->deces()->exists()) {
                throw new \Exception('Impossible de supprimer cette cause de décès : elle est utilisée dans des enregistrements de décès.');
            }
        });
    }

    /**
     * Generate a unique cause code.
     */
    protected static function generateUniqueCode(): string
    {
        $prefix = 'CAUSE_';
        
        $latest = static::where('code', 'like', $prefix . '%')
            ->orderBy('code', 'desc')
            ->first();

        if ($latest) {
            $number = (int) str_replace($prefix, '', $latest->code) + 1;
        } else {
            $number = 1;
        }

        return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
    }

    // ========== SCOPES ==========

    /**
     * Scope a query to only include active causes.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('est_actif', true);
    }

    /**
     * Scope a query to search causes by libelle or code.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('libelle', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhere('categorie', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('categorie', $category);
    }

    /**
     * Scope a query to filter by avoidable causes.
     */
    public function scopeAvoidable(Builder $query): Builder
    {
        return $query->where('est_evitable', true);
    }

    /**
     * Scope a query to filter by unavoidable causes.
     */
    public function scopeUnavoidable(Builder $query): Builder
    {
        return $query->where('est_evitable', false);
    }

    /**
     * Scope a query to filter by severity.
     */
    public function scopeBySeverity(Builder $query, int $severity): Builder
    {
        return $query->where('gravite', $severity);
    }

    /**
     * Scope a query to filter causes that affect a specific age.
     */
    public function scopeAffectsAge(Builder $query, int $age): Builder
    {
        return $query->where(function ($q) use ($age) {
            $q->whereNull('age_min_affecte')
              ->orWhere('age_min_affecte', '<=', $age);
        })->where(function ($q) use ($age) {
            $q->whereNull('age_max_affecte')
              ->orWhere('age_max_affecte', '>=', $age);
        });
    }

    /**
     * Scope a query to order by severity.
     */
    public function scopeOrderBySeverity(Builder $query, string $direction = 'desc'): Builder
    {
        return $query->orderBy('gravite', $direction);
    }

    /**
     * Scope a query to order by usage frequency.
     */
    public function scopeOrderByUsage(Builder $query, string $direction = 'desc'): Builder
    {
        return $query->withCount('deces')->orderBy('deces_count', $direction);
    }

    // ========== RELATIONS ==========

    /**
     * Get all décès associated with this cause.
     */
    public function deces(): HasMany
    {
        return $this->hasMany(Deces::class, 'cause_deces_id');
    }

    // ========== ACCESSORS ==========

    /**
     * Get the count of décès for this cause.
     */
    public function getDecesCountAttribute(): int
    {
        return $this->deces()->count();
    }

    /**
     * Get the severity level as text.
     */
    public function getGraviteTextAttribute(): string
    {
        switch($this->gravite) {
            case 1:
                return 'Faible';
            case 2:
                return 'Moyenne';
            case 3:
                return 'Élevée';
            case 4:
                return 'Critique';
            default:
                return 'Non définie';
        }
    }

    /**
     * Get the age range as text.
     */
    public function getTrancheAgeAttribute(): string
    {
        if ($this->age_min_affecte && $this->age_max_affecte) {
            return "{$this->age_min_affecte} - {$this->age_max_affecte} ans";
        } elseif ($this->age_min_affecte) {
            return "≥ {$this->age_min_affecte} ans";
        } elseif ($this->age_max_affecte) {
            return "≤ {$this->age_max_affecte} ans";
        }
        
        return 'Tous âges';
    }

    /**
     * Get the avoidable status as text.
     */
    public function getEvitableTextAttribute(): string
    {
        return $this->est_evitable ? 'Évitable' : 'Inévitable';
    }

    /**
     * Get the full identifier (code + libelle).
     */
    public function getFullIdentifierAttribute(): string
    {
        return "{$this->code} - {$this->libelle}";
    }

    /**
     * Check if the cause can be deleted.
     */
    public function getCanBeDeletedAttribute(): bool
    {
        return !$this->deces()->exists();
    }

    /**
     * Get the usage percentage among all deaths.
     */
    public function getUsagePercentageAttribute(): float
    {
        $totalDeces = Deces::count();
        if ($totalDeces === 0) {
            return 0.0;
        }

        return round(($this->deces_count / $totalDeces) * 100, 2);
    }

    // ========== METHODS ==========

    /**
     * Get statistics for this cause.
     */
    public function getStatistics(): array
    {
        $currentYear = now()->year;
        
        return [
            'total_deces' => $this->deces_count,
            'deces_this_year' => $this->deces()->whereHas('deces', function ($q) use ($currentYear) {
                $q->where('ANNEE_DECES', $currentYear);
            })->count(),
            'usage_percentage' => $this->usage_percentage,
            'average_age' => $this->deces()
                ->whereNotNull('ANNEE_DECES')
                ->whereNotNull('ANNEE_NAISSANCE_DEFUNT')
                ->avg(DB::raw('ANNEE_DECES - ANNEE_NAISSANCE_DEFUNT')),
            'male_count' => $this->deces()->where('SEXE_DEFUNT', 1)->count(),
            'female_count' => $this->deces()->where('SEXE_DEFUNT', 2)->count(),
        ];
    }

    /**
     * Get monthly statistics for a given year.
     */
    public function getMonthlyStatistics(int $year): array
    {
        return $this->deces()
            ->selectRaw('MOIS_DECES as month, COUNT(*) as total')
            ->where('ANNEE_DECES', $year)
            ->whereNotNull('MOIS_DECES')
            ->groupBy('MOIS_DECES')
            ->orderBy('MOIS_DECES')
            ->get()
            ->pluck('total', 'month')
            ->toArray();
    }

    /**
     * Get related causes (same category).
     */
    public function getRelatedCauses(int $limit = 5): Collection
    {
        if (!$this->categorie) {
            return collect();
        }

        return static::where('categorie', $this->categorie)
            ->where('id', '!=', $this->id)
            ->active()
            ->orderByUsage()
            ->limit($limit)
            ->get();
    }

    /**
     * Activate the cause.
     */
    public function activate(): bool
    {
        return $this->update(['est_actif', true]);
    }

    /**
     * Deactivate the cause.
     */
    public function deactivate(): bool
    {
        return $this->update(['est_actif', false]);
    }

    /**
     * Check if the cause affects a specific age.
     */
    public function affectsAge(int $age): bool
    {
        $minOk = is_null($this->age_min_affecte) || $age >= $this->age_min_affecte;
        $maxOk = is_null($this->age_max_affecte) || $age <= $this->age_max_affecte;
        
        return $minOk && $maxOk;
    }

    // ========== STATIC METHODS ==========

    /**
     * Get all categories.
     */
    public static function getCategories(): array
    {
        return [
            'maladie_infectieuse' => 'Maladie infectieuse',
            'maladie_chronique' => 'Maladie chronique',
            'traumatisme' => 'Traumatisme',
            'cancer' => 'Cancer',
            'maladie_cardiovasculaire' => 'Maladie cardiovasculaire',
            'maladie_respiratoire' => 'Maladie respiratoire',
            'maladie_nerveuse' => 'Maladie nerveuse',
            'cause_externe' => 'Cause externe',
            'autre' => 'Autre',
        ];
    }

    /**
     * Get most frequent causes.
     */
    public static function getMostFrequent(int $limit = 10): Collection
    {
        return static::withCount('deces')
            ->active()
            ->orderBy('deces_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get causes by category statistics.
     */
    public static function getCategoryStatistics(): array
    {
        return static::selectRaw('categorie, COUNT(*) as total_causes, SUM(deces_count) as total_deces')
            ->leftJoinSub(
                Deces::selectRaw('cause_deces_id, COUNT(*) as deces_count')
                    ->groupBy('cause_deces_id'),
                'deces_stats',
                'causes_deces.id',
                '=',
                'deces_stats.cause_deces_id'
            )
            ->whereNotNull('categorie')
            ->groupBy('categorie')
            ->get()
            ->keyBy('categorie')
            ->toArray();
    }
}