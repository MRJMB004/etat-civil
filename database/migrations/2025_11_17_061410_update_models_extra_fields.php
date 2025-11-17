<?php
// database/migrations/2024_01_01_000003_update_models_extra_fields.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Table regions
        Schema::table('regions', function (Blueprint $table) {
            $table->text('description')->nullable()->after('libelle');
            $table->boolean('is_active')->default(true)->after('description');
            $table->index('is_active');
        });

        // Table districts
        Schema::table('districts', function (Blueprint $table) {
            $table->text('description')->nullable()->after('libelle');
            $table->boolean('is_active')->default(true)->after('description');
            $table->index('is_active');
        });

        // Table communes
        Schema::table('communes', function (Blueprint $table) {
            $table->integer('population')->nullable()->after('libelle');
            $table->decimal('superficie', 10, 2)->nullable()->after('population');
            $table->text('description')->nullable()->after('superficie');
            $table->boolean('is_active')->default(true)->after('description');
            $table->index('is_active');
            $table->index(['population', 'superficie']);
        });

        // Table fokontany
        Schema::table('fokontany', function (Blueprint $table) {
            $table->integer('population')->nullable()->after('libelle');
            $table->integer('nombre_menages')->nullable()->after('population');
            $table->string('chef_fokontany')->nullable()->after('nombre_menages');
            $table->string('contact_chef')->nullable()->after('chef_fokontany');
            $table->text('adresse')->nullable()->after('contact_chef');
            $table->text('description')->nullable()->after('adresse');
            $table->boolean('is_active')->default(true)->after('description');
            
            $table->index(['commune_id', 'is_active']);
            $table->index('code');
            $table->index('idfkt');
        });

        // Table causes_deces
        Schema::table('causes_deces', function (Blueprint $table) {
            $table->string('categorie')->nullable()->after('libelle');
            $table->boolean('est_evitable')->default(false)->after('description');
            $table->integer('gravite')->default(2)->after('est_evitable');
            $table->integer('age_min_affecte')->nullable()->after('gravite');
            $table->integer('age_max_affecte')->nullable()->after('age_min_affecte');
            $table->boolean('est_actif')->default(true)->after('age_max_affecte');
            
            $table->index(['categorie', 'est_actif']);
            $table->index('gravite');
            $table->index('est_evitable');
        });

        // Table nationalites
        Schema::table('nationalites', function (Blueprint $table) {
            $table->string('continent')->nullable()->after('libelle');
            $table->string('sous_continent')->nullable()->after('continent');
            $table->string('code_iso', 3)->nullable()->after('sous_continent');
            $table->boolean('est_dans_union')->default(false)->after('code_iso');
            $table->boolean('est_actif')->default(true)->after('est_dans_union');
            
            $table->index(['continent', 'est_actif']);
            $table->index('est_dans_union');
            $table->unique('code_iso');
        });

        // Table professions
        Schema::table('professions', function (Blueprint $table) {
            $table->string('categorie')->nullable()->after('libelle');
            $table->string('sous_categorie')->nullable()->after('categorie');
            $table->integer('niveau_qualification')->default(2)->after('sous_categorie');
            $table->string('secteur_activite')->nullable()->after('niveau_qualification');
            $table->boolean('est_reglementee')->default(false)->after('secteur_activite');
            $table->boolean('est_actif')->default(true)->after('est_reglementee');
            
            $table->index(['categorie', 'est_actif']);
            $table->index('niveau_qualification');
            $table->index('secteur_activite');
            $table->index('est_reglementee');
        });
    }

    public function down()
    {
        // Supprimer les colonnes ajoutées (dans l'ordre inverse)
        Schema::table('professions', function (Blueprint $table) {
            $table->dropColumn(['categorie', 'sous_categorie', 'niveau_qualification', 
                               'secteur_activite', 'est_reglementee', 'est_actif']);
        });

        Schema::table('nationalites', function (Blueprint $table) {
            $table->dropColumn(['continent', 'sous_continent', 'code_iso', 
                               'est_dans_union', 'est_actif']);
        });

        Schema::table('causes_deces', function (Blueprint $table) {
            $table->dropColumn(['categorie', 'est_evitable', 'gravite', 
                               'age_min_affecte', 'age_max_affecte', 'est_actif']);
        });

        Schema::table('fokontany', function (Blueprint $table) {
            $table->dropColumn(['population', 'nombre_menages', 'chef_fokontany', 
                               'contact_chef', 'adresse', 'description', 'is_active']);
        });

        Schema::table('communes', function (Blueprint $table) {
            $table->dropColumn(['population', 'superficie', 'description', 'is_active']);
        });

        Schema::table('districts', function (Blueprint $table) {
            $table->dropColumn(['description', 'is_active']);
        });

        Schema::table('regions', function (Blueprint $table) {
            $table->dropColumn(['description', 'is_active']);
        });
    }
};