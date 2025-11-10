<?php

namespace Database\Seeders;

use App\Models\Region;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = [
            ['code' => 'ANA', 'libelle' => 'Analamanga'],
            ['code' => 'VAK', 'libelle' => 'Vakinankaratra'],
            ['code' => 'ITO', 'libelle' => 'Itasy'],
            ['code' => 'BON', 'libelle' => 'Bongolava'],
            ['code' => 'MAT', 'libelle' => 'Matsiatra Ambony'],
            ['code' => 'AMO', 'libelle' => 'Amoron\'i Mania'],
            ['code' => 'VAT', 'libelle' => 'Vatovavy Fitovinany'],
            ['code' => 'IHO', 'libelle' => 'Ihorombe'],
            ['code' => 'ATS', 'libelle' => 'Atsimo-Atsinanana'],
            ['code' => 'ATN', 'libelle' => 'Atsinanana'],
            ['code' => 'ANJ', 'libelle' => 'Analanjirofo'],
            ['code' => 'ALA', 'libelle' => 'Alaotra-Mangoro'],
            ['code' => 'BOE', 'libelle' => 'Boeny'],
            ['code' => 'BET', 'libelle' => 'Betsiboka'],
            ['code' => 'MEL', 'libelle' => 'Melaky'],
            ['code' => 'SOF', 'libelle' => 'Sofia'],
            ['code' => 'DIA', 'libelle' => 'Diana'],
            ['code' => 'SAV', 'libelle' => 'Sava'],
            ['code' => 'AND', 'libelle' => 'Androy'],
            ['code' => 'ANO', 'libelle' => 'Anosy'],
            ['code' => 'ATM', 'libelle' => 'Atsimo-Andrefana'],
            ['code' => 'MEN', 'libelle' => 'Menabe'],
        ];

        foreach ($regions as $region) {
            Region::create($region);
        }

        $this->command->info('22 régions créées avec succès !');
    }
}