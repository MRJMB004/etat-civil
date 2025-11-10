<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deces_2020_24', function (Blueprint $table) {
            $table->id();
            
            // Relations avec les tables de référence
            $table->foreignId('region_id')->nullable()->constrained('regions')->onDelete('set null');
            $table->foreignId('district_id')->nullable()->constrained('districts')->onDelete('set null');
            $table->foreignId('commune_id')->nullable()->constrained('communes')->onDelete('set null');
            $table->foreignId('fokontany_id')->nullable()->constrained('fokontany')->onDelete('set null');
            
            $table->foreignId('cause_deces_id')->nullable()->constrained('causes_deces')->onDelete('set null');
            $table->foreignId('profession_defunt_id')->nullable()->constrained('professions')->onDelete('set null');
            $table->foreignId('profession_declarant_id')->nullable()->constrained('professions')->onDelete('set null');
            $table->foreignId('nationalite_id')->nullable()->constrained('nationalites')->onDelete('set null');
            
            // Informations sur l'année et les dates
            $table->float('ANNEE_DECES')->nullable();
            $table->float('ANNEE_DECL')->nullable();
            $table->float('ANNEE_NAISSANCE_DEFUNT')->nullable();
            $table->float('ANN_CLASS')->nullable();
            
            // Informations sur le décès
            $table->string('CAUSE_DECES', 255)->nullable();
            $table->string('LIB_CAUSE_DECES', 255)->nullable();
            $table->float('HEUR_DECES')->nullable();
            $table->float('MIN_DECES')->nullable();
            $table->float('JOUR_DECES')->nullable();
            $table->float('MOIS_DECES')->nullable();
            $table->float('MOMENT_DECES')->nullable();
            
            // Informations sur la déclaration
            $table->float('JOUR_DECL')->nullable();
            $table->float('MOIS_DECL')->nullable();
            $table->float('N_ACTE')->nullable();
            
            // Informations géographiques - Commune
            $table->float('COMMUNE')->nullable();
            $table->string('LIBCOM', 255)->nullable();
            $table->float('COM_DECE')->nullable();
            $table->string('COM_DECE_L', 255)->nullable();
            $table->float('COM_ACTUELLE_DECLARANT')->nullable();
            $table->string('COM_ACTUELLE_DECLARANT_L', 255)->nullable();
            $table->float('COM_ACTUELLE_DOMICILE')->nullable();
            $table->string('COM_ACTUELLE_DOMICILE_L', 255)->nullable();
            $table->float('COMMUNE_NAISSANCE_DEFUNT')->nullable();
            $table->string('COMMUNE_NAISSANCE_DEFUNT_L', 255)->nullable();
            
            // Informations géographiques - District
            $table->float('DISTRICT')->nullable();
            $table->string('LIBDIST', 255)->nullable();
            $table->float('DIST_DECE')->nullable();
            $table->string('DIST_DECE_L', 255)->nullable();
            $table->float('DIST_ACTUELLE_DECLARANT')->nullable();
            $table->string('DIST_ACTUELLE_DECLARANT_L', 255)->nullable();
            $table->float('DIST_ACTUEL_DEFUNU')->nullable();
            $table->string('DIST_ACTUEL_DEFUNU_L', 255)->nullable();
            $table->float('DISTRICT_NAISSANCE_DEFUNT')->nullable();
            $table->string('DISTRICT_NAISSANCE_DEFUNT_L', 255)->nullable();
            
            // Informations géographiques - Fokontany
            $table->float('FOKONTANY')->nullable();
            $table->string('LIBFKT', 255)->nullable();
            $table->float('IDFKT')->nullable();
            $table->float('FOKONTANY_ACTUELLE_DOMICILE')->nullable();
            $table->string('FOKONTANY_ACTUELLE_DOMICILE_L', 255)->nullable();
            $table->float('FOKONTANY_NAISSANCE_DEFUNT')->nullable();
            $table->string('FOKONTANY_NAISSANCE_DEFUNT_L', 255)->nullable();
            
            // Informations géographiques - Région et autres
            $table->float('REGION')->nullable();
            $table->string('LIBREG', 255)->nullable();
            $table->float('MILIEU')->nullable();
            $table->string('LIBMIL', 255)->nullable();
            $table->float('SANITAIRE')->nullable();
            $table->string('DFIN', 255)->nullable();
            
            // Informations sur le défunt
            $table->enum('SEXE_DEFUNT', ['1', '2'])->nullable()->comment('1=Masculin, 2=Féminin');
            $table->float('NATIONALITE_DEFUNT')->nullable();
            $table->float('SITUATION_MATRIMONIAL_DEFUNT')->nullable();
            $table->float('PROFESSION_DEFUNT')->nullable();
            $table->string('PROFESSION_DEFUNT_L', 255)->nullable();
            $table->float('JOUR_NAISSANCE_DEFUNT')->nullable();
            $table->float('MOIS_NAISSANCE_DEFUNT')->nullable();
            
            // Informations sur le déclarant
            $table->float('LIEN_PAR_DECLARANT_DEFUNT')->nullable();
            $table->float('PROFESSION_DECLARANT')->nullable();
            $table->string('PROFESSION_DECLARANT_L', 255)->nullable();
            
            // Informations de classification
            $table->float('MOIS_CLASS')->nullable();
            
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('ANNEE_DECES');
            $table->index('SEXE_DEFUNT');
            $table->index('N_ACTE');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deces_2020_24');
    }
};