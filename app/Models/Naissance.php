<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Naissance extends Model
{
    use HasFactory;

    protected $table = 'naissance_2020_24';

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
    ];

    protected $casts = [
        'AGE_MERE' => 'float',
        'AGE_PERE' => 'float',
        'ANNEE_DECLARATION' => 'float',
        'ANNEE_EXACTE_ENREGISTREMENT_ACTE' => 'float',
        'ANNEE_NAISSANCE' => 'float',
        'ANNEE_NAISS_MERE' => 'float',
        'ANNEE_NAISS_PERE' => 'float',
        'ANNEE_REGISTRE' => 'float',
        'JOUR_DECLARATION' => 'float',
        'JOUR_NAISSANCE' => 'float',
        'JOUR_NAISS_MERE' => 'float',
        'JOUR_NAISS_PERE' => 'float',
        'JOUR_REGISTRE' => 'float',
        'MOIS_DECLARATION' => 'float',
        'MOIS_EXACT_ENREGISTREMENT_ACT' => 'float',
        'MOIS_NAISSANCE' => 'float',
        'MOIS_NAISS_MERE' => 'float',
        'MOIS_NAISS_PERE' => 'float',
        'MOIS_REGISTRE' => 'float',
        'HEUR_DE_NAISSANCE' => 'float',
        'MIN_DE_NAISSANCE' => 'float',
        'MOMENT_DE_NAISSANCE' => 'float',
        'COD_COM_LIEU_ACTUEL_MERE' => 'float',
        'COD_COM_LIEU_ACTUEL_PERE' => 'float',
        'COD_COM_LIEU_NAISS_MERE' => 'float',
        'COD_COM_LIEU_NAISS_PERE' => 'float',
        'COD_COM_RESID_DECLARANT' => 'float',
        'COMMUNE' => 'float',
        'COD_DIST_LIEU_ACTUEL_MERE' => 'float',
        'COD_DIST_LIEU_ACTUEL_PERE' => 'float',
        'COD_DIST_LIEU_NAISS_MERE' => 'float',
        'COD_DIST_LIEU_NAISS_PERE' => 'float',
        'COD_DIST_RESID_DECLARANT' => 'float',
        'DISTRICT' => 'float',
        'FOKONTANY' => 'float',
        'IDFKT' => 'float',
        'REGION' => 'float',
        'MILIEU' => 'float',
        'SEXE_ENFANT' => 'float',
        'NAISS_VIV_MORT_NE' => 'float',
        'NAISS_ASSIS_PERS_SANTE' => 'float',
        'NAISS_FORM_SANITAIRE' => 'float',
        'EXISTENCE_PERE' => 'float',
        'PROF_MERE' => 'float',
        'PROF_PERE' => 'float',
        'N_ACTE' => 'float',
        'LIEN_PARENTE_DELC' => 'float',
        'TYPE_ENREG' => 'float',
        'SFIN' => 'float',
    ];

    // ========== RELATIONS ==========

    /**
     * Une naissance appartient à une région
     */
    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    /**
     * Une naissance appartient à un district
     */
    public function district()
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Une naissance appartient à une commune
     */
    public function commune()
    {
        return $this->belongsTo(Commune::class);
    }

    /**
     * Une naissance appartient à un fokontany
     */
    public function fokontany()
    {
        return $this->belongsTo(Fokontany::class);
    }

    /**
     * Une naissance a une profession pour la mère
     */
    public function professionMere()
    {
        return $this->belongsTo(Profession::class, 'profession_mere_id');
    }

    /**
     * Une naissance a une profession pour le père
     */
    public function professionPere()
    {
        return $this->belongsTo(Profession::class, 'profession_pere_id');
    }

    /**
     * Une naissance a une nationalité pour la mère
     */
    public function nationaliteMere()
    {
        return $this->belongsTo(Nationalite::class, 'nationalite_mere_id');
    }

    /**
     * Une naissance a une nationalité pour le père
     */
    public function nationalitePere()
    {
        return $this->belongsTo(Nationalite::class, 'nationalite_pere_id');
    }

    // ========== ACCESSEURS ==========

    /**
     * Obtenir le sexe de l'enfant en texte
     */
    public function getSexeEnfantTextAttribute()
    {
        return $this->SEXE_ENFANT == 1 ? 'Masculin' : ($this->SEXE_ENFANT == 2 ? 'Féminin' : 'Non défini');
    }

    /**
     * Obtenir la date complète de naissance
     */
    public function getDateNaissanceCompleteAttribute()
    {
        if ($this->JOUR_NAISSANCE && $this->MOIS_NAISSANCE && $this->ANNEE_NAISSANCE) {
            return sprintf('%02d/%02d/%d', $this->JOUR_NAISSANCE, $this->MOIS_NAISSANCE, $this->ANNEE_NAISSANCE);
        }
        return 'Date non disponible';
    }

    /**
     * Obtenir l'heure complète de naissance
     */
    public function getHeureNaissanceCompleteAttribute()
    {
        if ($this->HEUR_DE_NAISSANCE !== null && $this->MIN_DE_NAISSANCE !== null) {
            return sprintf('%02d:%02d', $this->HEUR_DE_NAISSANCE, $this->MIN_DE_NAISSANCE);
        }
        return 'Heure non disponible';
    }

    /**
     * Obtenir la date de naissance de la mère
     */
    public function getDateNaissanceMereAttribute()
    {
        if ($this->JOUR_NAISS_MERE && $this->MOIS_NAISS_MERE && $this->ANNEE_NAISS_MERE) {
            return sprintf('%02d/%02d/%d', $this->JOUR_NAISS_MERE, $this->MOIS_NAISS_MERE, $this->ANNEE_NAISS_MERE);
        }
        return 'Date non disponible';
    }

    /**
     * Obtenir la date de naissance du père
     */
    public function getDateNaissancePereAttribute()
    {
        if ($this->JOUR_NAISS_PERE && $this->MOIS_NAISS_PERE && $this->ANNEE_NAISS_PERE) {
            return sprintf('%02d/%02d/%d', $this->JOUR_NAISS_PERE, $this->MOIS_NAISS_PERE, $this->ANNEE_NAISS_PERE);
        }
        return 'Date non disponible';
    }

    /**
     * Obtenir la date de déclaration
     */
    public function getDateDeclarationAttribute()
    {
        if ($this->JOUR_DECLARATION && $this->MOIS_DECLARATION && $this->ANNEE_DECLARATION) {
            return sprintf('%02d/%02d/%d', $this->JOUR_DECLARATION, $this->MOIS_DECLARATION, $this->ANNEE_DECLARATION);
        }
        return 'Date non disponible';
    }

    /**
     * Vérifier si l'enfant est né vivant
     */
    public function getEstNeVivantAttribute()
    {
        return $this->NAISS_VIV_MORT_NE == 1;
    }

    /**
     * Vérifier si l'accouchement a été assisté
     */
    public function getAccouchementAssisteAttribute()
    {
        return $this->NAISS_ASSIS_PERS_SANTE == 1;
    }

    /**
     * Vérifier si le père existe
     */
    public function getPereExisteAttribute()
    {
        return $this->EXISTENCE_PERE == 1;
    }

    /**
     * Obtenir le milieu de résidence en texte
     */
    public function getMilieuTextAttribute()
    {
        return $this->MILIEU == 1 ? 'Urbain' : ($this->MILIEU == 2 ? 'Rural' : 'Non défini');
    }

    /**
     * Obtenir le type d'enregistrement en texte
     */
    public function getTypeEnregistrementTextAttribute()
    {
        $types = [
            '1' => 'Normal',
            '2' => 'Tardif',
            '3' => 'Judiciaire',
        ];
        return $types[$this->TYPE_ENREG] ?? 'Non défini';
    }

    /**
     * Calculer l'âge actuel de l'enfant
     */
    public function getAgeEnfantAttribute()
    {
        if ($this->ANNEE_NAISSANCE) {
            return date('Y') - $this->ANNEE_NAISSANCE;
        }
        return null;
    }

    /**
     * Obtenir le lieu de naissance complet
     */
    public function getLieuNaissanceCompletAttribute()
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

    // ========== SCOPES ==========

    /**
     * Scope pour filtrer par année
     */
    public function scopeParAnnee($query, $annee)
    {
        return $query->where('ANNEE_NAISSANCE', $annee);
    }

    /**
     * Scope pour filtrer par mois
     */
    public function scopeParMois($query, $mois)
    {
        return $query->where('MOIS_NAISSANCE', $mois);
    }

    /**
     * Scope pour filtrer par sexe
     */
    public function scopeParSexe($query, $sexe)
    {
        return $query->where('SEXE_ENFANT', $sexe);
    }

    /**
     * Scope pour filtrer par région
     */
    public function scopeParRegion($query, $regionId)
    {
        return $query->where('region_id', $regionId);
    }

    /**
     * Scope pour filtrer par district
     */
    public function scopeParDistrict($query, $districtId)
    {
        return $query->where('district_id', $districtId);
    }

    /**
     * Scope pour filtrer par commune
     */
    public function scopeParCommune($query, $communeId)
    {
        return $query->where('commune_id', $communeId);
    }

    /**
     * Scope pour filtrer par fokontany
     */
    public function scopeParFokontany($query, $fokontanyId)
    {
        return $query->where('fokontany_id', $fokontanyId);
    }

    /**
     * Scope pour les naissances vivantes
     */
    public function scopeNaissancesVivantes($query)
    {
        return $query->where('NAISS_VIV_MORT_NE', 1);
    }

    /**
     * Scope pour les mort-nés
     */
    public function scopeMortNes($query)
    {
        return $query->where('NAISS_VIV_MORT_NE', 2);
    }

    /**
     * Scope pour les accouchements assistés
     */
    public function scopeAccouchementsAssistes($query)
    {
        return $query->where('NAISS_ASSIS_PERS_SANTE', 1);
    }

    /**
     * Scope pour les naissances en milieu urbain
     */
    public function scopeMilieuUrbain($query)
    {
        return $query->where('MILIEU', 1);
    }

    /**
     * Scope pour les naissances en milieu rural
     */
    public function scopeMilieuRural($query)
    {
        return $query->where('MILIEU', 2);
    }

    /**
     * Scope pour filtrer par tranche d'âge de la mère
     */
    public function scopeParTrancheAgeMere($query, $min, $max)
    {
        return $query->whereBetween('AGE_MERE', [$min, $max]);
    }

    /**
     * Scope pour filtrer par tranche d'âge du père
     */
    public function scopeParTrancheAgePere($query, $min, $max)
    {
        return $query->whereBetween('AGE_PERE', [$min, $max]);
    }

    /**
     * Scope pour les naissances avec père déclaré
     */
    public function scopeAvecPere($query)
    {
        return $query->where('EXISTENCE_PERE', 1);
    }

    /**
     * Scope pour les naissances sans père déclaré
     */
    public function scopeSansPere($query)
    {
        return $query->where('EXISTENCE_PERE', 2);
    }

    /**
     * Scope pour recherche globale
     */
    public function scopeRecherche($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('NOM_ENFANT', 'LIKE', "%{$search}%")
              ->orWhere('PRENOM_ENFANT', 'LIKE', "%{$search}%")
              ->orWhere('NOM_MERE', 'LIKE', "%{$search}%")
              ->orWhere('NOM_PERE', 'LIKE', "%{$search}%")
              ->orWhere('N_ACTE', 'LIKE', "%{$search}%")
              ->orWhere('LIBCOM', 'LIKE', "%{$search}%")
              ->orWhere('LIBDIST', 'LIKE', "%{$search}%")
              ->orWhere('LIBREG', 'LIKE', "%{$search}%")
              ->orWhere('LIBFKT', 'LIKE', "%{$search}%")
              ->orWhere('NATIONALITE_MERE', 'LIKE', "%{$search}%")
              ->orWhere('NATIONALITE_PERE', 'LIKE', "%{$search}%")
              ->orWhere('PROF_MERE_L', 'LIKE', "%{$search}%")
              ->orWhere('PROF_PERE_L', 'LIKE', "%{$search}%");
        });
    }

    /**
     * Scope pour filtrer par période
     */
    public function scopeParPeriode($query, $anneeDebut, $anneeFin)
    {
        return $query->whereBetween('ANNEE_NAISSANCE', [$anneeDebut, $anneeFin]);
    }

    /**
     * Scope pour charger toutes les relations
     */
    public function scopeAvecRelations($query)
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

    // ========== MÉTHODES STATIQUES ==========

    /**
     * Obtenir les statistiques par année
     */
    public static function statistiquesParAnnee()
    {
        return self::selectRaw('ANNEE_NAISSANCE as annee, COUNT(*) as total')
            ->whereNotNull('ANNEE_NAISSANCE')
            ->groupBy('ANNEE_NAISSANCE')
            ->orderBy('ANNEE_NAISSANCE', 'desc')
            ->get();
    }

    /**
     * Obtenir les statistiques par sexe
     */
    public static function statistiquesParSexe($annee = null)
    {
        $query = self::selectRaw('SEXE_ENFANT, COUNT(*) as total')
            ->whereNotNull('SEXE_ENFANT')
            ->groupBy('SEXE_ENFANT');

        if ($annee) {
            $query->where('ANNEE_NAISSANCE', $annee);
        }

        return $query->get();
    }

    /**
     * Obtenir les statistiques par région
     */
    public static function statistiquesParRegion($annee = null)
    {
        $query = self::selectRaw('REGION, COUNT(*) as total')
            ->whereNotNull('REGION')
            ->groupBy('REGION')
            ->orderBy('total', 'desc');

        if ($annee) {
            $query->where('ANNEE_NAISSANCE', $annee);
        }

        return $query->get();
    }

    /**
     * Obtenir le taux d'assistance médicale
     */
    public static function tauxAssistanceMedicale($annee = null)
    {
        $query = self::selectRaw('NAISS_ASSIS_PERS_SANTE, COUNT(*) as total')
            ->whereNotNull('NAISS_ASSIS_PERS_SANTE')
            ->groupBy('NAISS_ASSIS_PERS_SANTE');

        if ($annee) {
            $query->where('ANNEE_NAISSANCE', $annee);
        }

        $result = $query->get();
        
        $total = $result->sum('total');
        $assistes = $result->where('NAISS_ASSIS_PERS_SANTE', 1)->first()->total ?? 0;
        
        return $total > 0 ? round(($assistes / $total) * 100, 2) : 0;
    }

    /**
     * Obtenir la répartition par milieu
     */
    public static function repartitionParMilieu($annee = null)
    {
        $query = self::selectRaw('MILIEU, COUNT(*) as total')
            ->whereNotNull('MILIEU')
            ->groupBy('MILIEU');

        if ($annee) {
            $query->where('ANNEE_NAISSANCE', $annee);
        }

        return $query->get();
    }
}