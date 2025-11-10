<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Region;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer la région Analamanga
        $analamanga = Region::where('code', 'ANA')->first();

        if ($analamanga) {
            $districts = [
                ['region_id' => $analamanga->id, 'code' => 'ANT', 'libelle' => 'Antananarivo Renivohitra'],
                ['region_id' => $analamanga->id, 'code' => 'ANB', 'libelle' => 'Antananarivo Atsimondrano'],
                ['region_id' => $analamanga->id, 'code' => 'ANA', 'libelle' => 'Antananarivo Avaradrano'],
                ['region_id' => $analamanga->id, 'code' => 'ANZ', 'libelle' => 'Anjozorobe'],
                ['region_id' => $analamanga->id, 'code' => 'AMB', 'libelle' => 'Ambohidratrimo'],
                ['region_id' => $analamanga->id, 'code' => 'AND', 'libelle' => 'Andramasina'],
                ['region_id' => $analamanga->id, 'code' => 'MAN', 'libelle' => 'Manjakandriana'],
            ];

            foreach ($districts as $district) {
                District::create($district);
            }

            $this->command->info('Districts de Analamanga créés avec succès !');
        }

        // Récupérer la région Vakinankaratra
        $vakinankaratra = Region::where('code', 'VAK')->first();

        if ($vakinankaratra) {
            $districts = [
                ['region_id' => $vakinankaratra->id, 'code' => 'ANS', 'libelle' => 'Antsirabe I'],
                ['region_id' => $vakinankaratra->id, 'code' => 'AN2', 'libelle' => 'Antsirabe II'],
                ['region_id' => $vakinankaratra->id, 'code' => 'BET', 'libelle' => 'Betafo'],
                ['region_id' => $vakinankaratra->id, 'code' => 'FAR', 'libelle' => 'Faratsiho'],
                ['region_id' => $vakinankaratra->id, 'code' => 'AMT', 'libelle' => 'Ambatolampy'],
                ['region_id' => $vakinankaratra->id, 'code' => 'ANF', 'libelle' => 'Antanifotsy'],
            ];

            foreach ($districts as $district) {
                District::create($district);
            }

            $this->command->info('Districts de Vakinankaratra créés avec succès !');
        }

        $this->command->info('Tous les districts ont été créés !');
    }
}