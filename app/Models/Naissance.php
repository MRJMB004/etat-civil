<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Naissance extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'naissance_2020_24';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'region_id',
        'district_id', 
        'commune_id',
        'fokontany_id',
        'profession_mere_id',
        'profession_pere_id', 
        'nationalite_mere_id',
        'nationalite_pere_id',
        'AGE_MERE',
        'AGE_PERE',
        'ANNEE_DECLARATION',
        'ANNEE_EXACTE_ENREGISTREMENT_ACTE',
        'ANNEE_NAISSANCE',
        'ANNEE_NAISS_MERE',
        'ANNEE_NAISS_PERE',
        'ANNEE_REGISTRE',
        'JOUR_DECLARATION',
        'JOUR_NAISSANCE',
        'JOUR_NAISS_MERE', 
        'JOUR_NAISS_PERE',
        'JOUR_REGISTRE',
        'MOIS_DECLARATION',
        'MOIS_EXACT_ENREGISTREMENT_ACT',
        'MOIS_NAISSANCE',
        'MOIS_NAISS_MERE',
        'MOIS_NAISS_PERE',
        'MOIS_REGISTRE',
        'HEUR_DE_NAISSANCE',
        'MIN_DE_NAISSANCE',
        'MOMENT_DE_NAISSANCE',
        'COD_COM_LIEU_ACTUEL_MERE',
        'COD_COM_LIEU_ACTUEL_PERE',
        'COD_COM_LIEU_NAISS_MERE',
        'COD_COM_LIEU_NAISS_PERE',
        'COD_COM_RESID_DECLARANT',
        'COMMUNE',
        'LIBCOM',
        'LIB_COM_LIEU_ACTUEL_MERE',
        'LIB_COM_LIEU_ACTUEL_PERE', 
        'LIB_COM_LIEU_NAISS_MERE',
        'LIB_COM_LIEU_NAISS_PERE',
        'LIB_COM_RESID_DECLARANT',
        'COD_DIST_LIEU_ACTUEL_MERE',
        'COD_DIST_LIEU_ACTUEL_PERE',
        'COD_DIST_LIEU_NAISS_MERE',
        'COD_DIST_LIEU_NAISS_PERE',
        'COD_DIST_RESID_DECLARANT',
        'DISTRICT',
        'LIBDIST',
        'LIB_DIST_LIEU_ACTUEL_MERE',
        'LIB_DIST_LIEU_ACTUEL_PERE',
        'LIB_DIST_LIEU_NAISS_MERE',
        'LIB_DIST_LIEU_NAISS_PERE',
        'LIB_DIST_RESID_DECLARANT',
        'FOKONTANY',
        'LIBFKT',
        'IDFKT',
        'REGION', 
        'LIBREG',
        'MILIEU',
        'LIBMIL',
        'SEXE_ENFANT',
        'LIB_NAISSANCE_ENFANT',
        'NAISS_VIV_MORT_NE',
        'NAISS_ASSIS_PERS_SANTE',
        'NAISS_FORM_SANITAIRE',
        'EXISTENCE_PERE',
        'NATIONALITE_MERE',
        'NATIONALITE_PERE',
        'PROF_MERE',
        'PROF_MERE_L',
        'PROF_PERE',
        'PROF_PERE_L',
        'N_ACTE',
        'LIEN_PARENTE_DELC',
        'TYPE_ENREG',
        'SFIN',
        // Ajout des champs manquants pour la recherche
        'NOM_ENFANT',
        'PRENOM_ENFANT', 
        'NOM_MERE',
        'NOM_PERE',
        'created_by'  // AJOUTÉ ICI
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'AGE_MERE' => 'integer',
        'AGE_PERE' => 'integer',
        'ANNEE_DECLARATION' => 'integer',
        'ANNEE_EXACTE_ENREGISTREMENT_ACTE' => 'integer',
        'ANNEE_NAISSANCE' => 'integer',
        'ANNEE_NAISS_MERE' => 'integer',
        'ANNEE_NAISS_PERE' => 'integer',
        'ANNEE_REGISTRE' => 'integer',
        'JOUR_DECLARATION' => 'integer',
        'JOUR_NAISSANCE' => 'integer',
        'JOUR_NAISS_MERE' => 'integer',
        'JOUR_NAISS_PERE' => 'integer',
        'JOUR_REGISTRE' => 'integer',
        'MOIS_DECLARATION' => 'integer',
        'MOIS_EXACT_ENREGISTREMENT_ACT' => 'integer',
        'MOIS_NAISSANCE' => 'integer',
        'MOIS_NAISS_MERE' => 'integer',
        'MOIS_NAISS_PERE' => 'integer',
        'MOIS_REGISTRE' => 'integer',
        'HEUR_DE_NAISSANCE' => 'integer',
        'MIN_DE_NAISSANCE' => 'integer',
        'MOMENT_DE_NAISSANCE' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($naissance) {
            // Générer un numéro d'acte unique si non fourni
            if (empty($naissance->N_ACTE)) {
                $naissance->N_ACTE = static::generateNumeroActe();
            }
        });
    }

    /**
     * Generate a unique act number.
     */
    protected static function generateNumeroActe(): string
    {
        $prefix = 'ACT_N';
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
     * Get the region that owns the naissance.
     */
    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Get the district that owns the naissance.
     */
    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Get the commune that owns the naissance.
     */
    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class);
    }

    /**
     * Get the fokontany that owns the naissance.
     */
    public function fokontany(): BelongsTo
    {
        return $this->belongsTo(Fokontany::class);
    }

    /**
     * Get the profession of the mother.
     */
    public function professionMere(): BelongsTo
    {
        return $this->belongsTo(Profession::class, 'profession_mere_id');
    }

    /**
     * Get the profession of the father.
     */
    public function professionPere(): BelongsTo
    {
        return $this->belongsTo(Profession::class, 'profession_pere_id');
    }

    /**
     * Get the nationality of the mother.
     */
    public function nationaliteMere(): BelongsTo
    {
        return $this->belongsTo(Nationalite::class, 'nationalite_mere_id');
    }

    /**
     * Get the nationality of the father.
     */
    public function nationalitePere(): BelongsTo
    {
        return $this->belongsTo(Nationalite::class, 'nationalite_pere_id');
    }

    // ========== SCOPES ==========

    /**
     * Scope a query to search naissances.
     */
    public function scopeSearch(Builder $query, string $search): Builder
    {
        return $query->where(function ($q) use ($search) {
            $q->where('NOM_ENFANT', 'like', "%{$search}%")
              ->orWhere('PRENOM_ENFANT', 'like', "%{$search}%")
              ->orWhere('NOM_MERE', 'like', "%{$search}%")
              ->orWhere('NOM_PERE', 'like', "%{$search}%")
              ->orWhere('N_ACTE', 'like', "%{$search}%")
              ->orWhere('LIBCOM', 'like', "%{$search}%")
              ->orWhere('LIBDIST', 'like', "%{$search}%")
              ->orWhere('LIBREG', 'like', "%{$search}%");
        });
    }

    /**
     * Scope a query to filter by year.
     */
    public function scopeByYear(Builder $query, int $year): Builder
    {
        return $query->where('ANNEE_NAISSANCE', $year);
    }

    /**
     * Scope a query to filter by month.
     */
    public function scopeByMonth(Builder $query, int $month): Builder
    {
        return $query->where('MOIS_NAISSANCE', $month);
    }

    /**
     * Scope a query to filter by sex.
     */
    public function scopeBySex(Builder $query, int $sex): Builder
    {
        return $query->where('SEXE_ENFANT', $sex);
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
     * Scope a query to include all relations.
     */
    public function scopeWithRelations(Builder $query): Builder
    {
        return $query->with([
            'region',
            'district', 
            'commune',
            'fokontany',
            'professionMere',
            'professionPere',
            'nationaliteMere',
            'nationalitePere'
        ]);
    }

    /**
     * Scope a query for live births only.
     */
    public function scopeLiveBirths(Builder $query): Builder
    {
        return $query->where('NAISS_VIV_MORT_NE', 1);
    }

    /**
     * Scope a query for stillbirths only.
     */
    public function scopeStillbirths(Builder $query): Builder
    {
        return $query->where('NAISS_VIV_MORT_NE', 2);
    }

    /**
     * Scope a query for assisted deliveries.
     */
    public function scopeAssistedDeliveries(Builder $query): Builder
    {
        return $query->where('NAISS_ASSIS_PERS_SANTE', 1);
    }

    /**
     * Scope a query for urban area births.
     */
    public function scopeUrbanArea(Builder $query): Builder
    {
        return $query->where('MILIEU', 1);
    }

    /**
     * Scope a query for rural area births.
     */
    public function scopeRuralArea(Builder $query): Builder
    {
        return $query->where('MILIEU', 2);
    }

    /**
     * Scope a query to filter by mother's age range.
     */
    public function scopeByMotherAgeRange(Builder $query, int $min, int $max): Builder
    {
        return $query->whereBetween('AGE_MERE', [$min, $max]);
    }

    /**
     * Scope a query to filter by father's age range.
     */
    public function scopeByFatherAgeRange(Builder $query, int $min, int $max): Builder
    {
        return $query->whereBetween('AGE_PERE', [$min, $max]);
    }

    /**
     * Scope a query for births with declared father.
     */
    public function scopeWithFather(Builder $query): Builder
    {
        return $query->where('EXISTENCE_PERE', 1);
    }

    /**
     * Scope a query for births without declared father.
     */
    public function scopeWithoutFather(Builder $query): Builder
    {
        return $query->where('EXISTENCE_PERE', 2);
    }

    /**
     * Scope a query to filter by period.
     */
    public function scopeByPeriod(Builder $query, int $startYear, int $endYear): Builder
    {
        return $query->whereBetween('ANNEE_NAISSANCE', [$startYear, $endYear]);
    }

    // ========== ACCESSORS ==========

    /**
     * Get the child's sex as text.
     */
    public function getSexeEnfantTextAttribute(): string
    {
        switch($this->SEXE_ENFANT) {
            case 1:
                return 'Masculin';
            case 2:
                return 'Féminin';
            default:
                return 'Non spécifié';
        }
    }

    /**
     * Get the complete birth date.
     */
    public function getDateNaissanceCompleteAttribute(): ?string
    {
        if ($this->JOUR_NAISSANCE && $this->MOIS_NAISSANCE && $this->ANNEE_NAISSANCE) {
            return Carbon::create($this->ANNEE_NAISSANCE, $this->MOIS_NAISSANCE, $this->JOUR_NAISSANCE)
                ->format('d/m/Y');
        }
        return null;
    }

    /**
     * Get the complete birth time.
     */
    public function getHeureNaissanceCompleteAttribute(): ?string
    {
        if ($this->HEUR_DE_NAISSANCE !== null && $this->MIN_DE_NAISSANCE !== null) {
            return sprintf('%02d:%02d', $this->HEUR_DE_NAISSANCE, $this->MIN_DE_NAISSANCE);
        }
        return null;
    }

    /**
     * Get the mother's birth date.
     */
    public function getDateNaissanceMereAttribute(): ?string
    {
        if ($this->JOUR_NAISS_MERE && $this->MOIS_NAISS_MERE && $this->ANNEE_NAISS_MERE) {
            return Carbon::create($this->ANNEE_NAISS_MERE, $this->MOIS_NAISS_MERE, $this->JOUR_NAISS_MERE)
                ->format('d/m/Y');
        }
        return null;
    }

    /**
     * Get the father's birth date.
     */
    public function getDateNaissancePereAttribute(): ?string
    {
        if ($this->JOUR_NAISS_PERE && $this->MOIS_NAISS_PERE && $this->ANNEE_NAISS_PERE) {
            return Carbon::create($this->ANNEE_NAISS_PERE, $this->MOIS_NAISS_PERE, $this->JOUR_NAISS_PERE)
                ->format('d/m/Y');
        }
        return null;
    }

    /**
     * Get the declaration date.
     */
    public function getDateDeclarationAttribute(): ?string
    {
        if ($this->JOUR_DECLARATION && $this->MOIS_DECLARATION && $this->ANNEE_DECLARATION) {
            return Carbon::create($this->ANNEE_DECLARATION, $this->MOIS_DECLARATION, $this->JOUR_DECLARATION)
                ->format('d/m/Y');
        }
        return null;
    }

    /**
     * Check if the child was born alive.
     */
    public function getEstNeVivantAttribute(): bool
    {
        return $this->NAISS_VIV_MORT_NE == 1;
    }

    /**
     * Check if the delivery was assisted.
     */
    public function getAccouchementAssisteAttribute(): bool
    {
        return $this->NAISS_ASSIS_PERS_SANTE == 1;
    }

    /**
     * Check if father exists.
     */
    public function getPereExisteAttribute(): bool
    {
        return $this->EXISTENCE_PERE == 1;
    }

    /**
     * Get the residence area as text.
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
     * Get the registration type as text.
     */
    public function getTypeEnregistrementTextAttribute(): string
    {
        switch($this->TYPE_ENREG) {
            case 1:
                return 'Normal';
            case 2:
                return 'Tardif';
            case 3:
                return 'Judiciaire';
            default:
                return 'Non spécifié';
        }
    }

    /**
     * Calculate the child's current age.
     */
    public function getAgeEnfantAttribute(): ?int
    {
        if ($this->ANNEE_NAISSANCE) {
            return now()->year - $this->ANNEE_NAISSANCE;
        }
        return null;
    }

    /**
     * Get the complete birth location.
     */
    public function getLieuNaissanceCompletAttribute(): string
    {
        $lieu = [];
        
        if ($this->LIBFKT) $lieu[] = "Fokontany: {$this->LIBFKT}";
        if ($this->LIBCOM) $lieu[] = "Commune: {$this->LIBCOM}";
        if ($this->LIBDIST) $lieu[] = "District: {$this->LIBDIST}";
        if ($this->LIBREG) $lieu[] = "Région: {$this->LIBREG}";
        
        return $lieu ? implode(', ', $lieu) : 'Lieu non spécifié';
    }

    /**
     * Get the child's full name.
     */
    public function getNomCompletEnfantAttribute(): string
    {
        return trim($this->NOM_ENFANT . ' ' . $this->PRENOM_ENFANT);
    }

    /**
     * Get the mother's full name.
     */
    public function getNomCompletMereAttribute(): string
    {
        return $this->NOM_MERE ?? 'Non spécifié';
    }

    /**
     * Get the father's full name.
     */
    public function getNomCompletPereAttribute(): string
    {
        return $this->NOM_PERE ?? 'Non spécifié';
    }

    // ========== STATISTICAL METHODS ==========

    /**
     * Get statistics by year.
     */
    public static function getStatisticsByYear(): array
    {
        return self::selectRaw('ANNEE_NAISSANCE as year, COUNT(*) as total')
            ->whereNotNull('ANNEE_NAISSANCE')
            ->groupBy('ANNEE_NAISSANCE')
            ->orderBy('ANNEE_NAISSANCE', 'desc')
            ->get()
            ->pluck('total', 'year')
            ->toArray();
    }

    /**
     * Get statistics by sex for a given year.
     */
    public static function getStatisticsBySex(?int $year = null): array
    {
        $query = self::selectRaw('SEXE_ENFANT as sex, COUNT(*) as total')
            ->whereNotNull('SEXE_ENFANT');

        if ($year) {
            $query->where('ANNEE_NAISSANCE', $year);
        }

        return $query->groupBy('SEXE_ENFANT')
            ->get()
            ->pluck('total', 'sex')
            ->toArray();
    }

    /**
     * Get statistics by region for a given year.
     */
    public static function getStatisticsByRegion(?int $year = null): array
    {
        $query = self::selectRaw('REGION, LIBREG, COUNT(*) as total')
            ->whereNotNull('REGION');

        if ($year) {
            $query->where('ANNEE_NAISSANCE', $year);
        }

        return $query->groupBy('REGION', 'LIBREG')
            ->orderBy('total', 'desc')
            ->get()
            ->toArray();
    }

    /**
     * Get medical assistance rate for a given year.
     */
    public static function getMedicalAssistanceRate(?int $year = null): float
    {
        $query = self::whereNotNull('NAISS_ASSIS_PERS_SANTE');

        if ($year) {
            $query->where('ANNEE_NAISSANCE', $year);
        }

        $total = $query->count();
        $assisted = $query->where('NAISS_ASSIS_PERS_SANTE', 1)->count();

        return $total > 0 ? round(($assisted / $total) * 100, 2) : 0;
    }

    /**
     * Get distribution by area type for a given year.
     */
    public static function getDistributionByArea(?int $year = null): array
    {
        $query = self::selectRaw('MILIEU, COUNT(*) as total')
            ->whereNotNull('MILIEU');

        if ($year) {
            $query->where('ANNEE_NAISSANCE', $year);
        }

        return $query->groupBy('MILIEU')
            ->get()
            ->pluck('total', 'MILIEU')
            ->toArray();
    }

    /**
     * Get monthly statistics for a given year.
     */
    public static function getMonthlyStatistics(int $year): array
    {
        return self::selectRaw('MOIS_NAISSANCE as month, COUNT(*) as total')
            ->where('ANNEE_NAISSANCE', $year)
            ->whereNotNull('MOIS_NAISSANCE')
            ->groupBy('MOIS_NAISSANCE')
            ->orderBy('MOIS_NAISSANCE')
            ->get()
            ->pluck('total', 'month')
            ->toArray();
    }

    /**
     * Get recent births.
     */
    public static function getRecentBirths(int $limit = 10): array
    {
        return self::withRelations()
            ->latest('created_at')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}