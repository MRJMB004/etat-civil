<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Deces extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'deces_2020_24';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // Foreign keys
        'region_id',
        'district_id',
        'commune_id',
        'fokontany_id',
        'cause_deces_id',
        'profession_defunt_id',
        'profession_declarant_id',
        'nationalite_id',
        
        // Year and date information
        'ANNEE_DECES',
        'ANNEE_DECL',
        'ANNEE_NAISSANCE_DEFUNT',
        'ANN_CLASS',
        
        // Death information
        'CAUSE_DECES',
        'LIB_CAUSE_DECES',
        'HEUR_DECES',
        'MIN_DECES',
        'JOUR_DECES',
        'MOIS_DECES',
        'MOMENT_DECES',
        
        // Declaration information
        'JOUR_DECL',
        'MOIS_DECL',
        'N_ACTE',
        
        // Geographical information - Commune
        'COMMUNE',
        'LIBCOM',
        'COM_DECE',
        'COM_DECE_L',
        'COM_ACTUELLE_DECLARANT',
        'COM_ACTUELLE_DECLARANT_L',
        'COM_ACTUELLE_DOMICILE',
        'COM_ACTUELLE_DOMICILE_L',
        'COMMUNE_NAISSANCE_DEFUNT',
        'COMMUNE_NAISSANCE_DEFUNT_L',
        
        // Geographical information - District
        'DISTRICT',
        'LIBDIST',
        'DIST_DECE',
        'DIST_DECE_L',
        'DIST_ACTUELLE_DECLARANT',
        'DIST_ACTUELLE_DECLARANT_L',
        'DIST_ACTUEL_DEFUNU',
        'DIST_ACTUEL_DEFUNU_L',
        'DISTRICT_NAISSANCE_DEFUNT',
        'DISTRICT_NAISSANCE_DEFUNT_L',
        
        // Geographical information - Fokontany
        'FOKONTANY',
        'LIBFKT',
        'IDFKT',
        'FOKONTANY_ACTUELLE_DOMICILE',
        'FOKONTANY_ACTUELLE_DOMICILE_L',
        'FOKONTANY_NAISSANCE_DEFUNT',
        'FOKONTANY_NAISSANCE_DEFUNT_L',
        
        // Geographical information - Region and others
        'REGION',
        'LIBREG',
        'MILIEU',
        'LIBMIL',
        'SANITAIRE',
        'DFIN',
        
        // Deceased information
        'SEXE_DEFUNT',
        'NATIONALITE_DEFUNT',
        'SITUATION_MATRIMONIAL_DEFUNT',
        'PROFESSION_DEFUNT',
        'PROFESSION_DEFUNT_L',
        'JOUR_NAISSANCE_DEFUNT',
        'MOIS_NAISSANCE_DEFUNT',
        
        // Declarant information
        'LIEN_PAR_DECLARANT_DEFUNT',
        'PROFESSION_DECLARANT',
        'PROFESSION_DECLARANT_L',
        
        // Classification information
        'MOIS_CLASS',
        'created_by'  // AJOUTÉ ICI
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'ANNEE_DECES' => 'integer',
        'ANNEE_DECL' => 'integer',
        'ANNEE_NAISSANCE_DEFUNT' => 'integer',
        'ANN_CLASS' => 'integer',
        'HEUR_DECES' => 'integer',
        'MIN_DECES' => 'integer',
        'JOUR_DECES' => 'integer',
        'MOIS_DECES' => 'integer',
        'MOMENT_DECES' => 'integer',
        'JOUR_DECL' => 'integer',
        'MOIS_DECL' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($deces) {
            // Generate unique act number if not provided
            if (empty($deces->N_ACTE)) {
                $deces->N_ACTE = static::generateNumeroActe();
            }
        });
    }

    /**
     * Generate a unique act number.
     */
    protected static function generateNumeroActe(): string
    {
        $prefix = 'ACT_D';
        $year = date('Y');
        
        $latest = static::where('N_ACTE', 'like', $prefix . $year . '%')
            ->orderBy('N_ACTE', 'desc')
            ->first();

        if ($latest) {
            $number = (int) str_replace($prefix . $year, '', $latest->N_ACTE) + 1;
        } else {
            $number = 1;
        }

        return $prefix . $year . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    // ========== RELATIONS ==========

    /**
     * Get the region that owns the death.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Get the district that owns the death.
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Get the commune that owns the death.
     */
    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class);
    }

    /**
     * Get the fokontany that owns the death.
     */
    public function fokontany(): BelongsTo
    {
        return $this->belongsTo(Fokontany::class);
    }

    /**
     * Get the cause of death.
     */
    public function causeDeces(): BelongsTo
    {
        return $this->belongsTo(CauseDeces::class);
    }

    /**
     * Get the profession of the deceased.
     */
    public function professionDefunt(): BelongsTo
    {
        return $this->belongsTo(Profession::class, 'profession_defunt_id');
    }

    /**
     * Get the profession of the declarant.
     */
    public function professionDeclarant(): BelongsTo
    {
        return $this->belongsTo(Profession::class, 'profession_declarant_id');
    }

    /**
     * Get the nationality.
     */
    public function nationalite(): BelongsTo
    {
        return $this->belongsTo(Nationalite::class);
    }

    // ========== SCOPES ==========

    /**
     * Scope a query to search deaths.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('LIBCOM', 'like', "%{$search}%")
              ->orWhere('LIBDIST', 'like', "%{$search}%")
              ->orWhere('LIBREG', 'like', "%{$search}%")
              ->orWhere('LIBFKT', 'like', "%{$search}%")
              ->orWhere('N_ACTE', 'like', "%{$search}%")
              ->orWhere('LIB_CAUSE_DECES', 'like', "%{$search}%")
              ->orWhere('PROFESSION_DEFUNT_L', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to filter by year.
     */
    public function scopeByYear(Builder $query, int $year): Builder
    {
        return $query->where('ANNEE_DECES', $year);
    }

    /**
     * Scope a query to filter by month.
     */
    public function scopeByMonth(Builder $query, int $month): Builder
    {
        return $query->where('MOIS_DECES', $month);
    }

    /**
     * Scope a query to filter by sex.
     */
    public function scopeBySex(Builder $query, int $sex): Builder
    {
        return $query->where('SEXE_DEFUNT', $sex);
    }

    /**
     * Scope a query to filter by region.
     */
    public function scopeByRegion(Builder $query, int $regionId): Builder
    {
        return $query->where('region_id', $regionId);
    }

    /**
     * Scope a query to filter by district.
     */
    public function scopeByDistrict(Builder $query, int $districtId): Builder
    {
        return $query->where('district_id', $districtId);
    }

    /**
     * Scope a query to filter by commune.
     */
    public function scopeByCommune(Builder $query, int $communeId): Builder
    {
        return $query->where('commune_id', $communeId);
    }

    /**
     * Scope a query to filter by fokontany.
     */
    public function scopeByFokontany(Builder $query, int $fokontanyId): Builder
    {
        return $query->where('fokontany_id', $fokontanyId);
    }

    /**
     * Scope a query to filter by age range.
     */
    public function scopeByAgeRange(Builder $query, int $min, int $max): Builder
    {
        return $query->whereNotNull('ANNEE_DECES')
                    ->whereNotNull('ANNEE_NAISSANCE_DEFUNT')
                    ->whereRaw('(ANNEE_DECES - ANNEE_NAISSANCE_DEFUNT) BETWEEN ? AND ?', [$min, $max]);
    }

    /**
     * Scope a query for male deaths only.
     */
    public function scopeMale(Builder $query): Builder
    {
        return $query->where('SEXE_DEFUNT', 1);
    }

    /**
     * Scope a query for female deaths only.
     */
    public function scopeFemale(Builder $query): Builder
    {
        return $query->where('SEXE_DEFUNT', 2);
    }

    /**
     * Scope a query for hospital deaths only.
     */
    public function scopeHospitalDeaths(Builder $query): Builder
    {
        return $query->where('SANITAIRE', 1);
    }

    /**
     * Scope a query to include all relations.
     */
    public function scopeWithRelations(Builder $query): Builder
    {
        return $query->with([
            'region',
            'district',
            'commune',
            'fokontany',
            'causeDeces',
            'professionDefunt',
            'professionDeclarant',
            'nationalite'
        ]);
    }

    /**
     * Scope a query for recent deaths (last 30 days).
     */
    public function scopeRecent(Builder $query): Builder
    {
        return $query->where('created_at', '>=', now()->subDays(30));
    }

    /**
     * Scope a query to filter by period.
     */
    public function scopeByPeriod(Builder $query, int $startYear, int $endYear): Builder
    {
        return $query->whereBetween('ANNEE_DECES', [$startYear, $endYear]);
    }

    /**
     * Scope a query for urban area deaths.
     */
    public function scopeUrbanArea(Builder $query): Builder
    {
        return $query->where('MILIEU', 1);
    }

    /**
     * Scope a query for rural area deaths.
     */
    public function scopeRuralArea(Builder $query): Builder
    {
        return $query->where('MILIEU', 2);
    }

    /**
     * Scope a query to filter by cause of death.
     */
    public function scopeByCause(Builder $query, int $causeId): Builder
    {
        return $query->where('cause_deces_id', $causeId);
    }

    // ========== ACCESSORS ==========

    /**
     * Get the deceased's sex as text.
     */
    public function getSexeDefuntTextAttribute(): string
    {
        switch($this->SEXE_DEFUNT) {
            case 1:
                return 'Masculin';
            case 2:
                return 'Féminin';
            default:
                return 'Non spécifié';
        }
    }

    /**
     * Get the complete death date.
     */
    public function getDateDecesCompleteAttribute(): ?string
    {
        if ($this->JOUR_DECES && $this->MOIS_DECES && $this->ANNEE_DECES) {
            return Carbon::create($this->ANNEE_DECES, $this->MOIS_DECES, $this->JOUR_DECES)
                ->format('d/m/Y');
        }
        return null;
    }

    /**
     * Get the complete death time.
     */
    public function getHeureDecesCompleteAttribute(): ?string
    {
        if ($this->HEUR_DECES !== null && $this->MIN_DECES !== null) {
            return sprintf('%02d:%02d', $this->HEUR_DECES, $this->MIN_DECES);
        }
        return null;
    }

    /**
     * Calculate the deceased's age at death.
     */
    public function getAgeDefuntAttribute(): ?int
    {
        if ($this->ANNEE_DECES && $this->ANNEE_NAISSANCE_DEFUNT) {
            return $this->ANNEE_DECES - $this->ANNEE_NAISSANCE_DEFUNT;
        }
        return null;
    }

    /**
     * Get the deceased's birth date.
     */
    public function getDateNaissanceDefuntAttribute(): ?string
    {
        if ($this->JOUR_NAISSANCE_DEFUNT && $this->MOIS_NAISSANCE_DEFUNT && $this->ANNEE_NAISSANCE_DEFUNT) {
            return Carbon::create($this->ANNEE_NAISSANCE_DEFUNT, $this->MOIS_NAISSANCE_DEFUNT, $this->JOUR_NAISSANCE_DEFUNT)
                ->format('d/m/Y');
        }
        return null;
    }

    /**
     * Get the declaration date.
     */
    public function getDateDeclarationAttribute(): ?string
    {
        if ($this->JOUR_DECL && $this->MOIS_DECL && $this->ANNEE_DECL) {
            return Carbon::create($this->ANNEE_DECL, $this->MOIS_DECL, $this->JOUR_DECL)
                ->format('d/m/Y');
        }
        return null;
    }

    /**
     * Check if death occurred in hospital.
     */
    public function getDecesHopitalAttribute(): bool
    {
        return $this->SANITAIRE == 1;
    }

    /**
     * Get the complete death location.
     */
    public function getLieuDecesCompletAttribute(): string
    {
        $lieu = [];
        
        if ($this->LIBFKT) $lieu[] = "Fokontany: {$this->LIBFKT}";
        if ($this->LIBCOM) $lieu[] = "Commune: {$this->LIBCOM}";
        if ($this->LIBDIST) $lieu[] = "District: {$this->LIBDIST}";
        if ($this->LIBREG) $lieu[] = "Région: {$this->LIBREG}";
        
        return $lieu ? implode(', ', $lieu) : 'Lieu non spécifié';
    }

    /**
     * Get the area type as text.
     */
    public function getMilieuTextAttribute(): string
    {
        switch($this->MILIEU) {
            case 1:
                return 'Urbain';
            case 2:
                return 'Rural';
            default:
                return 'Non spécifié';
        }
    }

    /**
     * Get the marital status as text.
     */
    public function getSituationMatrimonialeTextAttribute(): string
    {
        switch($this->SITUATION_MATRIMONIAL_DEFUNT) {
            case 1:
                return 'Célibataire';
            case 2:
                return 'Marié(e)';
            case 3:
                return 'Divorcé(e)';
            case 4:
                return 'Veuf/Veuve';
            default:
                return 'Non spécifié';
        }
    }

    /**
     * Get the relationship with declarant as text.
     */
    public function getLienDeclarantTextAttribute(): string
    {
        switch($this->LIEN_PAR_DECLARANT_DEFUNT) {
            case 1:
                return 'Conjoint';
            case 2:
                return 'Enfant';
            case 3:
                return 'Parent';
            case 4:
                return 'Frère/Sœur';
            case 5:
                return 'Autre parent';
            case 6:
                return 'Non parent';
            default:
                return 'Non spécifié';
        }
    }

    // ========== STATISTICAL METHODS ==========

    /**
     * Get statistics by year.
     */
    public static function getStatisticsByYear(): array
    {
        return self::selectRaw('ANNEE_DECES as year, COUNT(*) as total')
            ->whereNotNull('ANNEE_DECES')
            ->groupBy('ANNEE_DECES')
            ->orderBy('ANNEE_DECES', 'desc')
            ->get()
            ->pluck('total', 'year')
            ->toArray();
    }

    /**
     * Get statistics by sex for a given year.
     */
    public static function getStatisticsBySex(?int $year = null): array
    {
        $query = self::selectRaw('SEXE_DEFUNT as sex, COUNT(*) as total')
            ->whereNotNull('SEXE_DEFUNT');

        if ($year) {
            $query->where('ANNEE_DECES', $year);
        }

        return $query->groupBy('SEXE_DEFUNT')
            ->get()
            ->pluck('total', 'sex')
            ->toArray();
    }

    /**
     * Get most frequent causes of death.
     */
    public static function getMostFrequentCauses(int $limit = 10): Collection
    {
        return self::selectRaw('LIB_CAUSE_DECES as cause, COUNT(*) as total')
            ->whereNotNull('LIB_CAUSE_DECES')
            ->groupBy('LIB_CAUSE_DECES')
            ->orderBy('total', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get age pyramid statistics.
     */
    public static function getAgePyramid(): Collection
    {
        return self::selectRaw('
                CASE 
                    WHEN (ANNEE_DECES - ANNEE_NAISSANCE_DEFUNT) < 1 THEN "0-1 an"
                    WHEN (ANNEE_DECES - ANNEE_NAISSANCE_DEFUNT) BETWEEN 1 AND 4 THEN "1-4 ans"
                    WHEN (ANNEE_DECES - ANNEE_NAISSANCE_DEFUNT) BETWEEN 5 AND 14 THEN "5-14 ans"
                    WHEN (ANNEE_DECES - ANNEE_NAISSANCE_DEFUNT) BETWEEN 15 AND 24 THEN "15-24 ans"
                    WHEN (ANNEE_DECES - ANNEE_NAISSANCE_DEFUNT) BETWEEN 25 AND 34 THEN "25-34 ans"
                    WHEN (ANNEE_DECES - ANNEE_NAISSANCE_DEFUNT) BETWEEN 35 AND 44 THEN "35-44 ans"
                    WHEN (ANNEE_DECES - ANNEE_NAISSANCE_DEFUNT) BETWEEN 45 AND 54 THEN "45-54 ans"
                    WHEN (ANNEE_DECES - ANNEE_NAISSANCE_DEFUNT) BETWEEN 55 AND 64 THEN "55-64 ans"
                    WHEN (ANNEE_DECES - ANNEE_NAISSANCE_DEFUNT) >= 65 THEN "65+ ans"
                    ELSE "Non défini"
                END as age_group,
                SEXE_DEFUNT as sex,
                COUNT(*) as total
            ')
            ->whereNotNull('ANNEE_DECES')
            ->whereNotNull('ANNEE_NAISSANCE_DEFUNT')
            ->whereNotNull('SEXE_DEFUNT')
            ->groupBy('age_group', 'SEXE_DEFUNT')
            ->orderByRaw('
                CASE age_group
                    WHEN "0-1 an" THEN 1
                    WHEN "1-4 ans" THEN 2
                    WHEN "5-14 ans" THEN 3
                    WHEN "15-24 ans" THEN 4
                    WHEN "25-34 ans" THEN 5
                    WHEN "35-44 ans" THEN 6
                    WHEN "45-54 ans" THEN 7
                    WHEN "55-64 ans" THEN 8
                    WHEN "65+ ans" THEN 9
                    ELSE 10
                END
            ')
            ->get();
    }

    /**
     * Get monthly statistics for a given year.
     */
    public static function getMonthlyStatistics(int $year): array
    {
        return self::selectRaw('MOIS_DECES as month, COUNT(*) as total')
            ->where('ANNEE_DECES', $year)
            ->whereNotNull('MOIS_DECES')
            ->groupBy('MOIS_DECES')
            ->orderBy('MOIS_DECES')
            ->get()
            ->pluck('total', 'month')
            ->toArray();
    }

    /**
     * Get death rate by region for a given year.
     */
    public static function getDeathRateByRegion(?int $year = null): Collection
    {
        $query = self::selectRaw('REGION, LIBREG, COUNT(*) as total')
            ->whereNotNull('REGION')
            ->whereNotNull('LIBREG');

        if ($year) {
            $query->where('ANNEE_DECES', $year);
        }

        return $query->groupBy('REGION', 'LIBREG')
            ->orderBy('total', 'desc')
            ->get();
    }

    /**
     * Get recent deaths with relations.
     */
    public static function getRecentDeaths(int $limit = 10): Collection
    {
        return self::withRelations()
            ->latest('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get hospital death rate for a given year.
     */
    public static function getHospitalDeathRate(?int $year = null): float
    {
        $query = self::whereNotNull('SANITAIRE');

        if ($year) {
            $query->where('ANNEE_DECES', $year);
        }

        $total = $query->count();
        $hospitalDeaths = $query->where('SANITAIRE', 1)->count();

        return $total > 0 ? round(($hospitalDeaths / $total) * 100, 2) : 0;
    }
}