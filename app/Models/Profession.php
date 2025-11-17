<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class Profession extends Model
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
        'categorie',
        'sous_categorie',
        'niveau_qualification',
        'secteur_activite',
        'est_reglementee',
        'est_actif'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'niveau_qualification' => 'integer',
        'est_reglementee' => 'boolean',
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

        static::creating(function ($profession) {
            // Générer un code unique si non fourni
            if (empty($profession->code)) {
                $profession->code = static::generateUniqueCode();
            }
        });

        // Empêcher la suppression si la profession est utilisée
        static::deleting(function ($profession) {
            if ($profession->isUsed()) {
                throw new \Exception('Impossible de supprimer cette profession : elle est utilisée dans des enregistrements.');
            }
        });
    }

    /**
     * Generate a unique profession code.
     */
    protected static function generateUniqueCode(): string
    {
        $prefix = 'PRO_';
        
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
     * Scope a query to only include active professions.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('est_actif', true);
    }

    /**
     * Scope a query to search professions by libelle or code.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('libelle', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%")
              ->orWhere('categorie', 'like', "%{$search}%")
              ->orWhere('sous_categorie', 'like', "%{$search}%")
              ->orWhere('secteur_activite', 'like', "%{$search}%");
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
     * Scope a query to filter by sub-category.
     */
    public function scopeBySubCategory(Builder $query, string $subCategory): Builder
    {
        return $query->where('sous_categorie', $subCategory);
    }

    /**
     * Scope a query to filter by activity sector.
     */
    public function scopeBySector(Builder $query, string $sector): Builder
    {
        return $query->where('secteur_activite', $sector);
    }

    /**
     * Scope a query to filter by qualification level.
     */
    public function scopeByQualification(Builder $query, int $level): Builder
    {
        return $query->where('niveau_qualification', $level);
    }

    /**
     * Scope a query to filter regulated professions.
     */
    public function scopeRegulated(Builder $query): Builder
    {
        return $query->where('est_reglementee', true);
    }

    /**
     * Scope a query to filter non-regulated professions.
     */
    public function scopeNotRegulated(Builder $query): Builder
    {
        return $query->where('est_reglementee', false);
    }

    /**
     * Scope a query to order by usage frequency.
     */
    public function scopeOrderByUsage(Builder $query, string $direction = 'desc'): Builder
    {
        return $query->withCount([
            'decesDefunts',
            'decesDeclarants', 
            'naissancesMeres',
            'naissancesPeres'
        ])->orderByRaw(
            "(deces_defunts_count + deces_declarants_count + naissances_meres_count + naissances_peres_count) {$direction}"
        );
    }

    /**
     * Scope a query to include usage statistics.
     */
    public function scopeWithUsageStats(Builder $query): Builder
    {
        return $query->withCount([
            'decesDefunts',
            'decesDeclarants',
            'naissancesMeres', 
            'naissancesPeres'
        ]);
    }

    /**
     * Scope a query for high qualification professions.
     */
    public function scopeHighQualification(Builder $query): Builder
    {
        return $query->where('niveau_qualification', '>=', 4);
    }

    /**
     * Scope a query for low qualification professions.
     */
    public function scopeLowQualification(Builder $query): Builder
    {
        return $query->where('niveau_qualification', '<=', 2);
    }

    // ========== RELATIONS ==========

    /**
     * Get all décès where this profession is for the deceased.
     */
    public function decesDefunts(): HasMany
    {
        return $this->hasMany(Deces::class, 'profession_defunt_id');
    }

    /**
     * Get all décès where this profession is for the declarant.
     */
    public function decesDeclarants(): HasMany
    {
        return $this->hasMany(Deces::class, 'profession_declarant_id');
    }

    /**
     * Get all naissances where mother has this profession.
     */
    public function naissancesMeres(): HasMany
    {
        return $this->hasMany(Naissance::class, 'profession_mere_id');
    }

    /**
     * Get all naissances where father has this profession.
     */
    public function naissancesPeres(): HasMany
    {
        return $this->hasMany(Naissance::class, 'profession_pere_id');
    }

    // ========== ACCESSORS ==========

    /**
     * Get the total count of deces for this profession.
     */
    public function getDecesTotalCountAttribute(): int
    {
        return $this->deces_defunts_count + $this->deces_declarants_count;
    }

    /**
     * Get the total count of naissances for this profession.
     */
    public function getNaissancesTotalCountAttribute(): int
    {
        return $this->naissances_meres_count + $this->naissances_peres_count;
    }

    /**
     * Get the total count of all records for this profession.
     */
    public function getTotalUsageCountAttribute(): int
    {
        return $this->deces_total_count + $this->naissances_total_count;
    }

    /**
     * Get the full identifier (code + libelle).
     */
    public function getFullIdentifierAttribute(): string
    {
        return "{$this->code} - {$this->libelle}";
    }

    /**
     * Get the qualification level as text.
     */
    public function getNiveauQualificationTextAttribute(): string
    {
        switch($this->niveau_qualification) {
            case 1:
                return 'Non qualifié';
            case 2:
                return 'Qualifié';
            case 3:
                return 'Technicien';
            case 4:
                return 'Supérieur';
            case 5:
                return 'Cadre';
            default:
                return 'Non spécifié';
        }
    }

    /**
     * Get the regulated status as text.
     */
    public function getReglementeeTextAttribute(): string
    {
        return $this->est_reglementee ? 'Réglementée' : 'Non réglementée';
    }

    /**
     * Get the complete category path.
     */
    public function getCategorieCompleteAttribute(): string
    {
        $parts = [];
        if ($this->sous_categorie) {
            $parts[] = $this->sous_categorie;
        }
        if ($this->categorie) {
            $parts[] = $this->categorie;
        }
        
        return $parts ? implode(' > ', $parts) : 'Non catégorisée';
    }

    /**
     * Check if the profession can be deleted.
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
        $totalRecords = Deces::count() * 2 + Naissance::count() * 2; // Défunts + déclarants + mères + pères
        if ($totalRecords === 0) {
            return 0.0;
        }

        return round(($this->total_usage_count / $totalRecords) * 100, 2);
    }

    /**
     * Get the gender distribution for this profession.
     */
    public function getDistributionGenreAttribute(): array
    {
        $maleDeaths = $this->decesDefunts()->where('SEXE_DEFUNT', 1)->count();
        $femaleDeaths = $this->decesDefunts()->where('SEXE_DEFUNT', 2)->count();
        
        $maleBirths = $this->naissancesPeres()->count(); // Pères sont masculins
        $femaleBirths = $this->naissancesMeres()->count(); // Mères sont féminines

        return [
            'hommes' => $maleDeaths + $maleBirths,
            'femmes' => $femaleDeaths + $femaleBirths,
            'total' => $this->total_usage_count,
        ];
    }

    // ========== METHODS ==========

    /**
     * Check if the profession is used in any records.
     */
    public function isUsed(): bool
    {
        return $this->decesDefunts()->exists() || 
               $this->decesDeclarants()->exists() || 
               $this->naissancesMeres()->exists() || 
               $this->naissancesPeres()->exists();
    }

    /**
     * Get detailed statistics for this profession.
     */
    public function getStatistics(): array
    {
        $currentYear = now()->year;
        
        return [
            'total_deces_defunts' => $this->deces_defunts_count,
            'total_deces_declarants' => $this->deces_declarants_count,
            'total_naissances_meres' => $this->naissances_meres_count,
            'total_naissances_peres' => $this->naissances_peres_count,
            'total_usage' => $this->total_usage_count,
            'usage_percentage' => $this->usage_percentage,
            
            'deces_defunts_this_year' => $this->decesDefunts()->where('ANNEE_DECES', $currentYear)->count(),
            'naissances_meres_this_year' => $this->naissancesMeres()->where('ANNEE_NAISSANCE', $currentYear)->count(),
            'naissances_peres_this_year' => $this->naissancesPeres()->where('ANNEE_NAISSANCE', $currentYear)->count(),
            
            'distribution_genre' => $this->distribution_genre,
            
            'average_death_age' => $this->decesDefunts()
                ->whereNotNull('ANNEE_DECES')
                ->whereNotNull('ANNEE_NAISSANCE_DEFUNT')
                ->avg(DB::raw('ANNEE_DECES - ANNEE_NAISSANCE_DEFUNT')),
                
            'common_regions' => $this->getCommonRegions(),
        ];
    }

    /**
     * Get most common regions for this profession.
     */
    public function getCommonRegions(int $limit = 5): Collection
    {
        // Régions des décès (défunts)
        $decesRegions = $this->decesDefunts()
            ->selectRaw('region_id, COUNT(*) as total')
            ->whereNotNull('region_id')
            ->groupBy('region_id');

        // Régions des naissances (pères)
        $naissancesPeresRegions = $this->naissancesPeres()
            ->selectRaw('region_id, COUNT(*) as total')
            ->whereNotNull('region_id')
            ->groupBy('region_id');

        // Combiner et agréger
        return $decesRegions->union($naissancesPeresRegions)
            ->get()
            ->groupBy('region_id')
            ->map(function ($items, $regionId) {
                return [
                    'region_id' => $regionId,
                    'total' => $items->sum('total')
                ];
            })
            ->sortByDesc('total')
            ->take($limit)
            ->values();
    }

    /**
     * Get yearly distribution for this profession.
     */
    public function getYearlyDistribution(string $type = 'all'): Collection
    {
        $query = null;

        switch ($type) {
            case 'deces_defunts':
                $query = $this->decesDefunts();
                $yearField = 'ANNEE_DECES';
                break;
            case 'naissances_meres':
                $query = $this->naissancesMeres();
                $yearField = 'ANNEE_NAISSANCE';
                break;
            case 'naissances_peres':
                $query = $this->naissancesPeres();
                $yearField = 'ANNEE_NAISSANCE';
                break;
            default:
                return $this->getCombinedYearlyDistribution();
        }

        return $query->selectRaw("{$yearField} as year, COUNT(*) as total")
            ->whereNotNull($yearField)
            ->groupBy($yearField)
            ->orderBy($yearField)
            ->get();
    }

    /**
     * Get combined yearly distribution for all record types.
     */
    protected function getCombinedYearlyDistribution(): Collection
    {
        $decesDefunts = $this->decesDefunts()
            ->selectRaw('ANNEE_DECES as year, "deces_defunts" as type, COUNT(*) as total')
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

        return $decesDefunts->union($naissancesMeres)->union($naissancesPeres)->get();
    }

    /**
     * Activate the profession.
     */
    public function activate(): bool
    {
        return $this->update(['est_actif' => true]);
    }

    /**
     * Deactivate the profession.
     */
    public function deactivate(): bool
    {
        return $this->update(['est_actif' => false]);
    }

    // ========== STATIC METHODS ==========

    /**
     * Get all categories.
     */
    public static function getCategories(): array
    {
        return [
            'agriculture' => 'Agriculture',
            'industrie' => 'Industrie',
            'construction' => 'Construction',
            'commerce' => 'Commerce',
            'transport' => 'Transport',
            'sante' => 'Santé',
            'education' => 'Éducation',
            'administration' => 'Administration',
            'technologie' => 'Technologie',
            'autre' => 'Autre',
        ];
    }

    /**
     * Get all activity sectors.
     */
    public static function getActivitySectors(): array
    {
        return [
            'primaire' => 'Secteur primaire',
            'secondaire' => 'Secteur secondaire', 
            'tertiaire' => 'Secteur tertiaire',
            'quaternaire' => 'Secteur quaternaire',
        ];
    }

    /**
     * Get most used professions.
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
     * Get professions by category statistics.
     */
    public static function getCategoryStatistics(): Collection
    {
        return static::selectRaw('categorie, COUNT(*) as total_professions, 
                SUM(deces_defunts_count) as total_deces_defunts,
                SUM(naissances_meres_count) as total_naissances_meres,
                SUM(naissances_peres_count) as total_naissances_peres')
            ->withUsageStats()
            ->whereNotNull('categorie')
            ->groupBy('categorie')
            ->orderBy('total_deces_defunts', 'desc')
            ->get();
    }

    /**
     * Get professions by qualification level statistics.
     */
    public static function getQualificationStatistics(): Collection
    {
        return static::selectRaw('niveau_qualification, COUNT(*) as total_professions, 
                AVG(deces_defunts_count) as avg_usage')
            ->withUsageStats()
            ->whereNotNull('niveau_qualification')
            ->groupBy('niveau_qualification')
            ->orderBy('niveau_qualification')
            ->get();
    }
}