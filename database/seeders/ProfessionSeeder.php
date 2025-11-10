<?php

namespace Database\Seeders;

use App\Models\Profession;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProfessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $professions = [
            ['code' => 'AGR', 'libelle' => 'Agriculteur'],
            ['code' => 'ENS', 'libelle' => 'Enseignant'],
            ['code' => 'COM', 'libelle' => 'Commerçant'],
            ['code' => 'FON', 'libelle' => 'Fonctionnaire'],
            ['code' => 'MED', 'libelle' => 'Médecin'],
            ['code' => 'INF', 'libelle' => 'Infirmier'],
            ['code' => 'CHA', 'libelle' => 'Chauffeur'],
            ['code' => 'ART', 'libelle' => 'Artisan'],
            ['code' => 'OUV', 'libelle' => 'Ouvrier'],
            ['code' => 'ING', 'libelle' => 'Ingénieur'],
            ['code' => 'SAN', 'libelle' => 'Sans profession'],
            ['code' => 'ETU', 'libelle' => 'Étudiant'],
            ['code' => 'RET', 'libelle' => 'Retraité'],
            ['code' => 'FAF', 'libelle' => 'Femme au foyer'],
            ['code' => 'CHE', 'libelle' => 'Chef d\'entreprise'],
            ['code' => 'JUR', 'libelle' => 'Juriste/Avocat'],
            ['code' => 'ARC', 'libelle' => 'Architecte'],
            ['code' => 'CUI', 'libelle' => 'Cuisinier'],
            ['code' => 'COI', 'libelle' => 'Coiffeur'],
            ['code' => 'MEC', 'libelle' => 'Mécanicien'],
            ['code' => 'ELE', 'libelle' => 'Électricien'],
            ['code' => 'MAÇ', 'libelle' => 'Maçon'],
            ['code' => 'MEN', 'libelle' => 'Menuisier'],
            ['code' => 'COU', 'libelle' => 'Couturier'],
            ['code' => 'PHA', 'libelle' => 'Pharmacien'],
        ];

        foreach ($professions as $profession) {
            Profession::create($profession);
        }

        $this->command->info('25 professions créées avec succès !');
    }
}