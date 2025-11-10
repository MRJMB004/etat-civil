<?php

namespace Database\Seeders;

use App\Models\Nationalite;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NationaliteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nationalites = [
            ['code' => 'MG', 'libelle' => 'Malgache'],
            ['code' => 'FR', 'libelle' => 'Française'],
            ['code' => 'CN', 'libelle' => 'Chinoise'],
            ['code' => 'IN', 'libelle' => 'Indienne'],
            ['code' => 'KM', 'libelle' => 'Comorienne'],
            ['code' => 'MU', 'libelle' => 'Mauricienne'],
            ['code' => 'RE', 'libelle' => 'Réunionnaise'],
            ['code' => 'US', 'libelle' => 'Américaine'],
            ['code' => 'GB', 'libelle' => 'Britannique'],
            ['code' => 'DE', 'libelle' => 'Allemande'],
            ['code' => 'IT', 'libelle' => 'Italienne'],
            ['code' => 'ES', 'libelle' => 'Espagnole'],
            ['code' => 'PT', 'libelle' => 'Portugaise'],
            ['code' => 'BE', 'libelle' => 'Belge'],
            ['code' => 'CH', 'libelle' => 'Suisse'],
            ['code' => 'CA', 'libelle' => 'Canadienne'],
            ['code' => 'JP', 'libelle' => 'Japonaise'],
            ['code' => 'KR', 'libelle' => 'Coréenne'],
            ['code' => 'PK', 'libelle' => 'Pakistanaise'],
            ['code' => 'LK', 'libelle' => 'Sri-lankaise'],
        ];

        foreach ($nationalites as $nationalite) {
            Nationalite::create($nationalite);
        }

        $this->command->info('20 nationalités créées avec succès !');
    }
}