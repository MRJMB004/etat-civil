<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('naissance_2020_24', function (Blueprint $table) {
            $table->id();
            
            // Relations avec les tables de référence
            $table->foreignId('region_id')->nullable()->constrained('regions')->onDelete('set null');
            $table->foreignId('district_id')->nullable()->constrained('districts')->onDelete('set null');
            $table->foreignId('commune_id')->nullable()->constrained('communes')->onDelete('set null');
            $table->foreignId('fokontany_id')->nullable()->constrained('fokontany')->onDelete('set null');
            
            $table->foreignId('profession_mere_id')->nullable()->constrained('professions')->onDelete('set null');
            $table->foreignId('profession_pere_id')->nullable()->constrained('professions')->onDelete('set null');
            $table->foreignId('nationalite_mere_id')->nullable()->constrained('nationalites')->onDelete('set null');
            $table->foreignId('nationalite_pere_id')->nullable()->constrained('nationalites')->onDelete('set null');
            
            // Informations sur les parents - Âge
            $table->float('AGE_MERE')->nullable();
            $table->float('AGE_PERE')->nullable();
            
            // Informations sur les dates de naissance
            $table->float('ANNEE_DECLARATION')->nullable();
            $table->float('ANNEE_EXACTE_ENREGISTREMENT_ACTE')->nullable();
            $table->float('ANNEE_NAISSANCE')->nullable();
            $table->float('ANNEE_NAISS_MERE')->nullable();
            $table->float('ANNEE_NAISS_PERE')->nullable();
            $table->float('ANNEE_REGISTRE')->nullable();
            
            // Détails des dates
            $table->float('JOUR_DECLARATION')->nullable();
            $table->float('JOUR_NAISSANCE')->nullable();
            $table->float('JOUR_NAISS_MERE')->nullable();
            $table->float('JOUR_NAISS_PERE')->nullable();
            $table->float('JOUR_REGISTRE')->nullable();
            
            $table->float('MOIS_DECLARATION')->nullable();
            $table->float('MOIS_EXACT_ENREGISTREMENT_ACT')->nullable();
            $table->float('MOIS_NAISSANCE')->nullable();
            $table->float('MOIS_NAISS_MERE')->nullable();
            $table->float('MOIS_NAISS_PERE')->nullable();
            $table->float('MOIS_REGISTRE')->nullable();
            
            // Heures de naissance
            $table->float('HEUR_DE_NAISSANCE')->nullable();
            $table->float('MIN_DE_NAISSANCE')->nullable();
            $table->float('MOMENT_DE_NAISSANCE')->nullable();
            
            // Codes géographiques - Commune
            $table->float('COD_COM_LIEU_ACTUEL_MERE')->nullable();
            $table->float('COD_COM_LIEU_ACTUEL_PERE')->nullable();
            $table->float('COD_COM_LIEU_NAISS_MERE')->nullable();
            $table->float('COD_COM_LIEU_NAISS_PERE')->nullable();
            $table->float('COD_COM_RESID_DECLARANT')->nullable();
            $table->float('COMMUNE')->nullable();
            $table->string('LIBCOM', 255)->nullable();
            
            // Libellés géographiques - Commune
            $table->string('LIB_COM_LIEU_ACTUEL_MERE', 255)->nullable();
            $table->string('LIB_COM_LIEU_ACTUEL_PERE', 255)->nullable();
            $table->string('LIB_COM_LIEU_NAISS_MERE', 255)->nullable();
            $table->string('LIB_COM_LIEU_NAISS_PERE', 255)->nullable();
            $table->string('LIB_COM_RESID_DECLARANT', 255)->nullable();
            
            // Codes géographiques - District
            $table->float('COD_DIST_LIEU_ACTUEL_MERE')->nullable();
            $table->float('COD_DIST_LIEU_ACTUEL_PERE')->nullable();
            $table->float('COD_DIST_LIEU_NAISS_MERE')->nullable();
            $table->float('COD_DIST_LIEU_NAISS_PERE')->nullable();
            $table->float('COD_DIST_RESID_DECLARANT')->nullable();
            $table->float('DISTRICT')->nullable();
            $table->string('LIBDIST', 255)->nullable();
            
            // Libellés géographiques - District
            $table->string('LIB_DIST_LIEU_ACTUEL_MERE', 255)->nullable();
            $table->string('LIB_DIST_LIEU_ACTUEL_PERE', 255)->nullable();
            $table->string('LIB_DIST_LIEU_NAISS_MERE', 255)->nullable();
            $table->string('LIB_DIST_LIEU_NAISS_PERE', 255)->nullable();
            $table->string('LIB_DIST_RESID_DECLARANT', 255)->nullable();
            
            // Informations géographiques - Fokontany
            $table->float('FOKONTANY')->nullable();
            $table->string('LIBFKT', 255)->nullable();
            $table->float('IDFKT')->nullable();
            
            // Informations géographiques - Région et Milieu
            $table->float('REGION')->nullable();
            $table->string('LIBREG', 255)->nullable();
            $table->float('MILIEU')->nullable();
            $table->string('LIBMIL', 255)->nullable();
            
            // Informations sur l'enfant
            $table->enum('SEXE_ENFANT', ['1', '2'])->nullable()->comment('1=Masculin, 2=Féminin');
            $table->string('LIB_NAISSANCE_ENFANT', 255)->nullable();
            $table->float('NAISS_VIV_MORT_NE')->nullable();
            
            // Informations sanitaires
            $table->float('NAISS_ASSIS_PERS_SANTE')->nullable();
            $table->float('NAISS_FORM_SANITAIRE')->nullable();
            
            // Informations sur les parents
            $table->float('EXISTENCE_PERE')->nullable();
            $table->float('NATIONALITE_MERE')->nullable();
            $table->float('NATIONALITE_PERE')->nullable();
            
            // Professions des parents
            $table->float('PROF_MERE')->nullable();
            $table->string('PROF_MERE_L', 255)->nullable();
            $table->float('PROF_PERE')->nullable();
            $table->string('PROF_PERE_L', 255)->nullable();
            
            // Informations administratives
            $table->float('N_ACTE')->nullable();
            $table->float('LIEN_PARENTE_DELC')->nullable();
            $table->float('TYPE_ENREG')->nullable();
            $table->string('SFIN', 255)->nullable();
            
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('ANNEE_NAISSANCE');
            $table->index('SEXE_ENFANT');
            $table->index('N_ACTE');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('naissance_2020_24');
    }
};