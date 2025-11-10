<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deces extends Model
{
    use HasFactory;

    /**
     * Le nom de la table associée au modèle.
     *
     * @var string
     */
    protected $table = 'deces_2020_24';

    /**
     * Les attributs qui sont assignables en masse.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        // Clés étrangères
        'region_id',
        'district_id',
        'commune_id',
        'fokontany_id',
        'cause_deces_id',
        'profession_defunt_id',
        'profession_declarant_id',
        'nationalite_id',
        
        // Informations sur l'année et les dates
        'ANNEE_DECES',
        'ANNEE_DECL',
        'ANNEE_NAISSANCE_DEFUNT',
        'ANN_CLASS',
        
        // Informations sur le décès
        'CAUSE_DECES',
        'LIB_CAUSE_DECES',
        'HEUR_DECES',
        'MIN_DECES',
        'JOUR_DECES',
        'MOIS_DECES',
        'MOMENT_DECES',
        
        // Informations sur la déclaration
        'JOUR_DECL',
        'MOIS_DECL',
        'N_ACTE',
        
        // Informations géographiques - Commune
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
        
        // Informations géographiques - District
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
        
        // Informations géographiques - Fokontany
        'FOKONTANY',
        'LIBFKT',
        'IDFKT',
        'FOKONTANY_ACTUELLE_DOMICILE',
        'FOKONTANY_ACTUELLE_DOMICILE_L',
        'FOKONTANY_NAISSANCE_DEFUNT',
        'FOKONTANY_NAISSANCE_DEFUNT_L',
        
        // Informations géographiques - Région et autres
        'REGION',
        'LIBREG',
        'MILIEU',
        'LIBMIL',
        'SANITAIRE',
        'DFIN',
        
        // Informations sur le défunt
        'SEXE_DEFUNT',
        'NATIONALITE_DEFUNT',
        'SITUATION_MATRIMONIAL_DEFUNT',
        'PROFESSION_DEFUNT',
        'PROFESSION_DEFUNT_L',
        'JOUR_NAISSANCE_DEFUNT',
        'MOIS_NAISSANCE_DEFUNT',
        
        // Informations sur le déclarant
        'LIEN_PAR_DECLARANT_DEFUNT',
        'PROFESSION_DECLARANT',
        'PROFESSION_DECLARANT_L',
        
        // Informations de classification
        'MOIS_CLASS',
    ];

    /**
     * Les attributs qui doivent être castés.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'ANNEE_DECES' => 'float',
        'ANNEE_DECL' => 'float',
        'ANNEE_NAISSANCE_DEFUNT' => 'float',
        'ANN_CLASS' => 'float',
        'HEUR_DECES' => 'float',
        'MIN_DECES' => 'float',
        'JOUR_DECES' => 'float',
        'MOIS_DECES' => 'float',
        'MOMENT_DECES' => 'float',
        'JOUR_DECL' => 'float',
        'MOIS_DECL' => 'float',
        'N_ACTE' => 'float',
        'COMMUNE' => 'float',
        'DISTRICT' => 'float',
        'REGION' => 'float',
        'FOKONTANY' => 'float',
        'IDFKT' => 'float',
        'MILIEU' => 'float',
        'SANITAIRE' => 'float',
        'SEXE_DEFUNT' => 'float',
        'NATIONALITE_DEFUNT' => 'float',
        'SITUATION_MATRIMONIAL_DEFUNT' => 'float',
        'PROFESSION_DEFUNT' => 'float',
        'JOUR_NAISSANCE_DEFUNT' => 'float',
        'MOIS_NAISSANCE_DEFUNT' => 'float',
        'LIEN_PAR_DECLARANT_DEFUNT' => 'float',
        'PROFESSION_DECLARANT' => 'float',
        'MOIS_CLASS' => 'float',
    ];

    // ========================================
    // RELATIONS AVEC LES TABLES DE RÉFÉRENCE
    // ========================================

    /**
     * Un décès appartient à une région
     */
    public function region()
    {
        return $this->belongsTo(Region::class, 'region_id');
    }

    /**
     * Un décès appartient à un district
     */
    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    /**
     * Un décès appartient à une commune
     */
    public function commune()
    {
        return $this->belongsTo(Commune::class, 'commune_id');
    }

    /**
     * Un décès appartient à un fokontany
     */
    public function fokontany()
    {
        return $this->belongsTo(Fokontany::class, 'fokontany_id');
    }

    /**
     * Un décès a une cause de décès
     */
    public function causeDeces()
    {
        return $this->belongsTo(CauseDeces::class, 'cause_deces_id');
    }

    /**
     * Un décès a une profession pour le défunt
     */
    public function professionDefunt()
    {
        return $this->belongsTo(Profession::class, 'profession_defunt_id');
    }

    /**
     * Un décès a une profession pour le déclarant
     */
    public function professionDeclarant()
    {
        return $this->belongsTo(Profession::class, 'profession_declarant_id');
    }

    /**
     * Un décès a une nationalité
     */
    public function nationalite()
    {
        return $this->belongsTo(Nationalite::class, 'nationalite_id');
    }

    // ========================================
    // ACCESSEURS (GETTERS)
    // ========================================

    /**
     * Obtenir le sexe du défunt en texte
     * 
     * @return string
     */
    public function getSexeDefuntTextAttribute(): string
    {
        if ($this->SEXE_DEFUNT == 1) {
            return 'Masculin';
        } elseif ($this->SEXE_DEFUNT == 2) {
            return 'Féminin';
        }
        return 'Non défini';
    }

    /**
     * Obtenir la date complète du décès au format DD/MM/YYYY
     * 
     * @return string
     */
    public function getDateDecesCompleteAttribute(): string
    {
        if ($this->JOUR_DECES && $this->MOIS_DECES && $this->ANNEE_DECES) {
            return sprintf(
                '%02d/%02d/%d', 
                $this->JOUR_DECES, 
                $this->MOIS_DECES, 
                $this->ANNEE_DECES
            );
        }
        return 'Date non disponible';
    }

    /**
     * Obtenir l'heure complète du décès au format HH:MM
     * 
     * @return string
     */
    public function getHeureDecesCompleteAttribute(): string
    {
        if ($this->HEUR_DECES !== null && $this->MIN_DECES !== null) {
            return sprintf('%02d:%02d', $this->HEUR_DECES, $this->MIN_DECES);
        }
        return 'Heure non disponible';
    }

    /**
     * Calculer l'âge du défunt au moment du décès
     * 
     * @return int|null
     */
    public function getAgeDefuntAttribute(): ?int
    {
        if ($this->ANNEE_DECES && $this->ANNEE_NAISSANCE_DEFUNT) {
            return (int)($this->ANNEE_DECES - $this->ANNEE_NAISSANCE_DEFUNT);
        }
        return null;
    }

    /**
     * Vérifier si le décès a eu lieu dans un établissement sanitaire
     * 
     * @return bool
     */
    public function getDecesHopitalAttribute(): bool
    {
        return $this->SANITAIRE == 1;
    }

    /**
     * Obtenir le nom complet du lieu de décès
     * 
     * @return string
     */
    public function getLieuDecesCompletAttribute(): string
    {
        $lieu = [];
        
        if ($this->LIBFKT) {
            $lieu[] = 'Fokontany: ' . $this->LIBFKT;
        }
        if ($this->LIBCOM) {
            $lieu[] = 'Commune: ' . $this->LIBCOM;
        }
        if ($this->LIBDIST) {
            $lieu[] = 'District: ' . $this->LIBDIST;
        }
        if ($this->LIBREG) {
            $lieu[] = 'Région: ' . $this->LIBREG;
        }
        
        return !empty($lieu) ? implode(', ', $lieu) : 'Lieu non spécifié';
    }

    // ========================================
    // SCOPES (FILTRES RÉUTILISABLES)
    // ========================================

    /**
     * Scope pour filtrer par année de décès
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $annee
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeParAnnee($query, int $annee)
    {
        return $query->where('ANNEE_DECES', $annee);
    }

    /**
     * Scope pour filtrer par mois
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $mois
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeParMois($query, int $mois)
    {
        return $query->where('MOIS_DECES', $mois);
    }

    /**
     * Scope pour filtrer par sexe
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $sexe
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeParSexe($query, int $sexe)
    {
        return $query->where('SEXE_DEFUNT', $sexe);
    }

    /**
     * Scope pour filtrer par région
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $regionId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeParRegion($query, int $regionId)
    {
        return $query->where('region_id', $regionId);
    }

    /**
     * Scope pour filtrer par district
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $districtId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeParDistrict($query, int $districtId)
    {
        return $query->where('district_id', $districtId);
    }

    /**
     * Scope pour filtrer par commune
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $communeId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeParCommune($query, int $communeId)
    {
        return $query->where('commune_id', $communeId);
    }

    /**
     * Scope pour filtrer les décès par tranche d'âge
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $min
     * @param int $max
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeParTrancheAge($query, int $min, int $max)
    {
        return $query->whereNotNull('ANNEE_DECES')
                    ->whereNotNull('ANNEE_NAISSANCE_DEFUNT')
                    ->whereRaw('(ANNEE_DECES - ANNEE_NAISSANCE_DEFUNT) BETWEEN ? AND ?', [$min, $max]);
    }

    /**
     * Scope pour les décès masculins uniquement
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeMasculins($query)
    {
        return $query->where('SEXE_DEFUNT', 1);
    }

    /**
     * Scope pour les décès féminins uniquement
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFeminins($query)
    {
        return $query->where('SEXE_DEFUNT', 2);
    }

    /**
     * Scope pour les décès survenus à l'hôpital
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeALhopital($query)
    {
        return $query->where('SANITAIRE', 1);
    }

    /**
     * Scope pour charger toutes les relations en une fois
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvecRelations($query)
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
     * Scope pour les décès récents (derniers 30 jours)
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecents($query)
    {
        return $query->where('created_at', '>=', now()->subDays(30));
    }

    /**
     * Scope pour recherche globale
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRecherche($query, string $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('LIBCOM', 'like', "%{$search}%")
              ->orWhere('LIBDIST', 'like', "%{$search}%")
              ->orWhere('LIBREG', 'like', "%{$search}%")
              ->orWhere('LIBFKT', 'like', "%{$search}%")
              ->orWhere('N_ACTE', 'like', "%{$search}%")
              ->orWhere('LIB_CAUSE_DECES', 'like', "%{$search}%");
        });
    }

    // ========================================
    // MÉTHODES STATIQUES UTILES
    // ========================================

    /**
     * Obtenir le nombre total de décès par année
     * 
     * @return \Illuminate\Support\Collection
     */
    public static function statistiquesParAnnee()
    {
        return self::selectRaw('ANNEE_DECES as annee, COUNT(*) as total')
            ->whereNotNull('ANNEE_DECES')
            ->groupBy('ANNEE_DECES')
            ->orderBy('ANNEE_DECES', 'desc')
            ->get();
    }

    /**
     * Obtenir le nombre de décès par sexe
     * 
     * @param int|null $annee
     * @return \Illuminate\Support\Collection
     */
    public static function statistiquesParSexe(?int $annee = null)
    {
        $query = self::selectRaw('SEXE_DEFUNT, COUNT(*) as total')
            ->whereNotNull('SEXE_DEFUNT')
            ->groupBy('SEXE_DEFUNT');

        if ($annee) {
            $query->where('ANNEE_DECES', $annee);
        }

        return $query->get();
    }

    /**
     * Obtenir les causes de décès les plus fréquentes
     * 
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public static function causesPlusFrequentes(int $limit = 10)
    {
        return self::selectRaw('LIB_CAUSE_DECES, COUNT(*) as total')
            ->whereNotNull('LIB_CAUSE_DECES')
            ->groupBy('LIB_CAUSE_DECES')
            ->orderBy('total', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Obtenir la pyramide des âges
     * 
     * @return \Illuminate\Support\Collection
     */
    public static function pyramideDesAges()
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
                END as tranche_age,
                COUNT(*) as total
            ')
            ->whereNotNull('ANNEE_DECES')
            ->whereNotNull('ANNEE_NAISSANCE_DEFUNT')
            ->groupBy('tranche_age')
            ->orderByRaw('MIN(ANNEE_DECES - ANNEE_NAISSANCE_DEFUNT)')
            ->get();
    }
}