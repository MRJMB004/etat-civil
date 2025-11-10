<?php

namespace Database\Seeders;

use App\Models\Fokontany;
use App\Models\Commune;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FokontanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer la commune Antananarivo
        $antananarivo = Commune::where('code', 'ANK')->first();

        if ($antananarivo) {
            $fokontany = [
                ['commune_id' => $antananarivo->id, 'code' => 'ANK01', 'libelle' => 'Analakely', 'idfkt' => 1],
                ['commune_id' => $antananarivo->id, 'code' => 'ANK02', 'libelle' => 'Andohalo', 'idfkt' => 2],
                ['commune_id' => $antananarivo->id, 'code' => 'ANK03', 'libelle' => 'Mahamasina', 'idfkt' => 3],
                ['commune_id' => $antananarivo->id, 'code' => 'ANK04', 'libelle' => 'Behoririka', 'idfkt' => 4],
                ['commune_id' => $antananarivo->id, 'code' => 'ANK05', 'libelle' => 'Ambohijatovo', 'idfkt' => 5],
                ['commune_id' => $antananarivo->id, 'code' => 'ANK06', 'libelle' => 'Isoraka', 'idfkt' => 6],
                ['commune_id' => $antananarivo->id, 'code' => 'ANK07', 'libelle' => 'Ampefiloha', 'idfkt' => 7],
                ['commune_id' => $antananarivo->id, 'code' => 'ANK08', 'libelle' => 'Ankadifotsy', 'idfkt' => 8],
                ['commune_id' => $antananarivo->id, 'code' => 'ANK09', 'libelle' => 'Isotry', 'idfkt' => 9],
                ['commune_id' => $antananarivo->id, 'code' => 'ANK10', 'libelle' => 'Ambohimiandra', 'idfkt' => 10],
                ['commune_id' => $antananarivo->id, 'code' => 'ANK11', 'libelle' => 'Ankazomanga', 'idfkt' => 11],
                ['commune_id' => $antananarivo->id, 'code' => 'ANK12', 'libelle' => 'Ankasina', 'idfkt' => 12],
                ['commune_id' => $antananarivo->id, 'code' => 'ANK13', 'libelle' => 'Anosy', 'idfkt' => 13],
                ['commune_id' => $antananarivo->id, 'code' => 'ANK14', 'libelle' => 'Ambanidia', 'idfkt' => 14],
                ['commune_id' => $antananarivo->id, 'code' => 'ANK15', 'libelle' => 'Ambatonakanga', 'idfkt' => 15],
            ];

            foreach ($fokontany as $fkt) {
                Fokontany::create($fkt);
            }

            $this->command->info('Fokontany de Antananarivo créés !');
        }

        $this->command->info('Tous les fokontany ont été créés !');
    }
}