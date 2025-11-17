<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class Nationalite extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'libelle',
        'continent',
        'sous_continent',
        'code_iso',
        'est_dans_union',
        'est_actif'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'est_dans_union' => 'boolean',
        'est_actif' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($nationalite) {
            // Générer un code unique si non fourni
            if (empty($nationalite->code)) {
                $nationalite->code = static::generateUniqueCode();
            }
            
            // Générer un code ISO si non fourni
            if (empty($nationalite->code_iso) && !empty($nationalite->libelle)) {
                $nationalite->code_iso = static::generateCodeIso($nationalite->libelle);
            }
        });

        // Empêcher la suppression si la nationalité est utilisée
        static::deleting(function ($nationalite) {
            if ($nationalite->isUsed()) {
                throw new \Exception('Impossible de supprimer cette nationalité : elle est utilisée dans des enregistrements.');
            }
        });
    }

    /**
     * Generate a unique nationality code.
     */
    protected static function generateUniqueCode(): string
    {
        $prefix = 'NAT_';
        
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

    /**
     * Generate ISO code from libelle.
     */
    protected static function generateCodeIso(string $libelle): string
    {
        // Simplification pour générer un code basé sur le nom
        $words = explode(' ', $libelle);
        $code = '';
        
        foreach ($words as $word) {
            if (strlen($word) > 0) {
                $code .= strtoupper(substr($word, 0, 1));
            }
        }
        
        return strlen($code) >= 2 ? substr($code, 0, 3) : str_pad($code, 3, 'X');
    }

    // ========== SCOPES ==========

    /**
     * Scope a query to only include active nationalities.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('est_actif', true);
    }

    /**
     * Scope a query to search nationalities by libelle or code.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('libelle', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhere('code_iso', 'like', "%{$search}%")
              ->orWhere('continent', 'like', "%{$search}%")
              ->orWhere('sous_continent', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to filter by continent.
     */
    public function scopeByContinent(Builder $query, string $continent): Builder
    {
        return $query->where('continent', $continent);
    }

    /**
     * Scope a query to filter by sub-continent.
     */
    public function scopeBySubContinent(Builder $query, string $subContinent): Builder
    {
        return $query->where('sous_continent', $subContinent);
    }

    /**
     * Scope a query to filter by union membership.
     */
    public function scopeInUnion(Builder $query): Builder
    {
        return $query->where('est_dans_union', true);
    }

    /**
     * Scope a query to filter by non-union membership.
     */
    public function scopeNotInUnion(Builder $query): Builder
    {
        return $query->where('est_dans_union', false);
    }

    /**
     * Scope a query to order by usage frequency.
     */
    public function scopeOrderByUsage(Builder $query, string $direction = 'desc'): Builder
    {
        return $query->select('nationalites.*')
            ->leftJoin('deces', 'nationalites.id', '=', 'deces.nationalite_id')
            ->groupBy('nationalites.id')
            ->orderByRaw("COUNT(deces.id) {$direction}");
    }

    /**
     * Scope a query to include usage statistics.
     */
    public function scopeWithUsageStats(Builder $query): Builder
    {
        return $query->withCount([
            'deces',
            'naissancesMeres as naissances_meres_count',
            'naissancesPeres as naissances_peres_count'
        ]);
    }

    // ========== RELATIONS ==========

    /**
     * Get all décès with this nationality.
     */
    public function deces(): HasMany
    {
        return $this->hasMany(Deces::class, 'nationalite_id');
    }

    /**
     * Get all naissances where mother has this nationality.
     */
    public function naissancesMeres(): HasMany
    {
        return $this->hasMany(Naissance::class, 'nationalite_mere_id');
    }

    /**
     * Get all naissances where father has this nationality.
     */
    public function naissancesPeres(): HasMany
    {
        return $this->hasMany(Naissance::class, 'nationalite_pere_id');
    }

    // ========== ACCESSORS ==========

    /**
     * Get the total count of naissances for this nationality.
     */
    public function getNaissancesTotalCountAttribute(): int
    {
        return $this->naissances_meres_count + $this->naissances_peres_count;
    }

    /**
     * Get the total count of all records for this nationality.
     */
    public function getTotalUsageCountAttribute(): int
    {
        return $this->deces_count + $this->naissances_total_count;
    }

    /**
     * Get the full identifier (code + libelle).
     */
    public function getFullIdentifierAttribute(): string
    {
        return "{$this->code} - {$this->libelle}";
    }

    /**
     * Get the union status as text.
     */
    public function getUnionStatusTextAttribute(): string
    {
        return $this->est_dans_union ? 'Membre' : 'Non membre';
    }

    /**
     * Get the geographical location.
     */
    public function getLocalisationAttribute(): string
    {
        $parts = [];
        if ($this->sous_continent) {
            $parts[] = $this->sous_continent;
        }
        if ($this->continent) {
            $parts[] = $this->continent;
        }
        
        return $parts ? implode(', ', $parts) : 'Non spécifié';
    }

    /**
     * Check if the nationality can be deleted.
     */
    public function getCanBeDeletedAttribute(): bool
    {
        return !$this->isUsed();
    }

    /**
     * Get the usage percentage among all records.
     */
    public function getUsagePercentageAttribute(): float
    {
        $totalRecords = Deces::count() + Naissance::count() * 2; // Mères et pères
        if ($totalRecords === 0) {
            return 0.0;
        }

        return round(($this->total_usage_count / $totalRecords) * 100, 2);
    }

    // ========== METHODS ==========

    /**
     * Check if the nationality is used in any records.
     */
    public function isUsed(): bool
    {
        return $this->deces()->exists() || 
               $this->naissancesMeres()->exists() || 
               $this->naissancesPeres()->exists();
    }

    /**
     * Get detailed statistics for this nationality.
     */
    public function getStatistics(): array
    {
        $currentYear = now()->year;
        
        return [
            'total_deces' => $this->deces_count,
            'total_naissances_meres' => $this->naissances_meres_count,
            'total_naissances_peres' => $this->naissances_peres_count,
            'total_usage' => $this->total_usage_count,
            'usage_percentage' => $this->usage_percentage,
            
            'deces_this_year' => $this->deces()->where('ANNEE_DECES', $currentYear)->count(),
            'naissances_meres_this_year' => $this->naissancesMeres()->where('ANNEE_NAISSANCE', $currentYear)->count(),
            'naissances_peres_this_year' => $this->naissancesPeres()->where('ANNEE_NAISSANCE', $currentYear)->count(),
            
            'male_deaths' => $this->deces()->where('SEXE_DEFUNT', 1)->count(),
            'female_deaths' => $this->deces()->where('SEXE_DEFUNT', 2)->count(),
            
            'average_death_age' => $this->deces()
                ->whereNotNull('ANNEE_DECES')
                ->whereNotNull('ANNEE_NAISSANCE_DEFUNT')
                ->avg(\DB::raw('ANNEE_DECES - ANNEE_NAISSANCE_DEFUNT')),
        ];
    }

    /**
     * Get distribution by year for this nationality.
     */
    public function getYearlyDistribution(string $type = 'all'): Collection
    {
        $query = null;

        switch ($type) {
            case 'deces':
                $query = $this->deces();
                break;
            case 'naissances_meres':
                $query = $this->naissancesMeres();
                break;
            case 'naissances_peres':
                $query = $this->naissancesPeres();
                break;
            default:
                // Combine all types
                return $this->getCombinedYearlyDistribution();
        }

        return $query->selectRaw('ANNEE_DECES as year, COUNT(*) as total')
            ->whereNotNull('ANNEE_DECES')
            ->groupBy('ANNEE_DECES')
            ->orderBy('ANNEE_DECES')
            ->get();
    }

    /**
     * Get combined yearly distribution for all record types.
     */
    protected function getCombinedYearlyDistribution(): Collection
    {
        $deces = $this->deces()
            ->selectRaw('ANNEE_DECES as year, "deces" as type, COUNT(*) as total')
            ->whereNotNull('ANNEE_DECES')
            ->groupBy('ANNEE_DECES');

        $naissancesMeres = $this->naissancesMeres()
            ->selectRaw('ANNEE_NAISSANCE as year, "naissances_meres" as type, COUNT(*) as total')
            ->whereNotNull('ANNEE_NAISSANCE')
            ->groupBy('ANNEE_NAISSANCE');

        $naissancesPeres = $this->naissancesPeres()
            ->selectRaw('ANNEE_NAISSANCE as year, "naissances_peres" as type, COUNT(*) as total')
            ->whereNotNull('ANNEE_NAISSANCE')
            ->groupBy('ANNEE_NAISSANCE');

        return $deces->union($naissancesMeres)->union($naissancesPeres)->get();
    }

    /**
     * Activate the nationality.
     */
    public function activate(): bool
    {
        return $this->update(['est_actif' => true]);
    }

    /**
     * Deactivate the nationality.
     */
    public function deactivate(): bool
    {
        return $this->update(['est_actif' => false]);
    }

    // ========== STATIC METHODS ==========

    /**
     * Get all continents.
     */
    public static function getContinents(): array
    {
        return [
            'afrique' => 'Afrique',
            'asie' => 'Asie',
            'europe' => 'Europe',
            'amerique_nord' => 'Amérique du Nord',
            'amerique_sud' => 'Amérique du Sud',
            'oceanie' => 'Océanie',
            'antartique' => 'Antarctique',
        ];
    }

    /**
     * Get most used nationalities.
     */
    public static function getMostUsed(int $limit = 10): Collection
    {
        return static::withUsageStats()
            ->active()
            ->get()
            ->sortByDesc('total_usage_count')
            ->take($limit);
    }

    /**
     * Get nationalities by continent statistics.
     */
    public static function getContinentStatistics(): Collection
    {
        return static::selectRaw('continent, COUNT(*) as total_nationalites, 
                SUM(deces_count) as total_deces,
                SUM(naissances_meres_count) as total_naissances_meres,
                SUM(naissances_peres_count) as total_naissances_peres')
            ->withUsageStats()
            ->whereNotNull('continent')
            ->groupBy('continent')
            ->orderBy('total_deces', 'desc')
            ->get();
    }

    /**
     * Get nationality by ISO code.
     */
    public static function findByIsoCode(string $isoCode): ?self
    {
        return static::where('code_iso', $isoCode)->first();
    }

    /**
     * Get local nationalities (within union).
     */
    public static function getLocalNationalities(): Collection
    {
        return static::inUnion()->active()->orderBy('libelle')->get();
    }

    /**
     * Get foreign nationalities (outside union).
     */
    public static function getForeignNationalities(): Collection
    {
        return static::notInUnion()->active()->orderBy('libelle')->get();
    }
}