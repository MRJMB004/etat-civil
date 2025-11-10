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
        'ANNEE_NAISSANCE' => 'float',
        'JOUR_NAISSANCE' => 'float',
        'MOIS_NAISSANCE' => 'float',
        'HEUR_DE_NAISSANCE' => 'float',
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

    // ========== SCOPES ==========

    /**
     * Scope pour filtrer par année
     */
    public function scopeParAnnee($query, $annee)
    {
        return $query->where('ANNEE_NAISSANCE', $annee);
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
}