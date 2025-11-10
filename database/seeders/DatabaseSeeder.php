<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌱 Démarrage du seeding de la base de données...');
        $this->command->newLine();

        // Tables de référence géographiques (dans l'ordre hiérarchique)
        $this->command->info('📍 Création des données géographiques...');
        $this->call(RegionSeeder::class);
        $this->call(DistrictSeeder::class);
        $this->call(CommuneSeeder::class);
        $this->call(FokontanySeeder::class);
        $this->command->newLine();

        // Tables de référence générales
        $this->command->info('📋 Création des données de référence...');
        $this->call(ProfessionSeeder::class);
        $this->call(NationaliteSeeder::class);
        $this->call(CauseDecesSeeder::class);
        $this->command->newLine();

        $this->command->info('✅ Seeding terminé avec succès !');
        $this->command->newLine();
    }
}