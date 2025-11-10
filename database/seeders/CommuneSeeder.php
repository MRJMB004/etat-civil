<?php

namespace Database\Seeders;

use App\Models\Commune;
use App\Models\District;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommuneSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer le district Antananarivo Renivohitra
        $antananarivoCentre = District::where('code', 'ANT')->first();

        if ($antananarivoCentre) {
            $communes = [
                ['district_id' => $antananarivoCentre->id, 'code' => 'ANK', 'libelle' => 'Antananarivo'],
            ];

            foreach ($communes as $commune) {
                Commune::create($commune);
            }

            $this->command->info('Communes de Antananarivo Renivohitra créées !');
        }

        // Récupérer le district Antananarivo Atsimondrano
        $atsimondrano = District::where('code', 'ANB')->first();

        if ($atsimondrano) {
            $communes = [
                ['district_id' => $atsimondrano->id, 'code' => 'AVT', 'libelle' => 'Antananarivo Atsimondrano'],
                ['district_id' => $atsimondrano->id, 'code' => 'TAL', 'libelle' => 'Talata Volonondry'],
                ['district_id' => $atsimondrano->id, 'code' => 'AMB', 'libelle' => 'Ambohidratrimo'],
            ];

            foreach ($communes as $commune) {
                Commune::create($commune);
            }

            $this->command->info('Communes de Antananarivo Atsimondrano créées !');
        }

        $this->command->info('Toutes les communes ont été créées !');
    }
}