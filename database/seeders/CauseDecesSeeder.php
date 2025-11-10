<?php

namespace Database\Seeders;

use App\Models\CauseDeces;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CauseDecesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $causes = [
            ['code' => 'C001', 'libelle' => 'Maladie cardiovasculaire', 'description' => 'Infarctus du myocarde, AVC, insuffisance cardiaque'],
            ['code' => 'C002', 'libelle' => 'Cancer', 'description' => 'Tous types de cancer (poumon, sein, foie, etc.)'],
            ['code' => 'C003', 'libelle' => 'Maladie respiratoire', 'description' => 'Pneumonie, tuberculose, asthme sévère'],
            ['code' => 'C004', 'libelle' => 'Accident de la route', 'description' => 'Collision, renversement, piéton'],
            ['code' => 'C005', 'libelle' => 'Paludisme', 'description' => 'Malaria grave'],
            ['code' => 'C006', 'libelle' => 'Diabète', 'description' => 'Complications du diabète sucré'],
            ['code' => 'C007', 'libelle' => 'Vieillesse', 'description' => 'Mort naturelle liée à l\'âge avancé'],
            ['code' => 'C008', 'libelle' => 'Infection', 'description' => 'Septicémie, méningite, etc.'],
            ['code' => 'C009', 'libelle' => 'Complication d\'accouchement', 'description' => 'Hémorragie, éclampsie, infection puerpérale'],
            ['code' => 'C010', 'libelle' => 'Noyade', 'description' => 'Accident aquatique'],
            ['code' => 'C011', 'libelle' => 'Suicide', 'description' => 'Mort volontaire'],
            ['code' => 'C012', 'libelle' => 'Homicide', 'description' => 'Meurtre, assassinat'],
            ['code' => 'C013', 'libelle' => 'Diarrhée', 'description' => 'Diarrhée sévère, déshydratation'],
            ['code' => 'C014', 'libelle' => 'Malnutrition', 'description' => 'Sous-nutrition sévère'],
            ['code' => 'C015', 'libelle' => 'VIH/SIDA', 'description' => 'Syndrome d\'immunodéficience acquise'],
            ['code' => 'C016', 'libelle' => 'Cirrhose du foie', 'description' => 'Maladie hépatique chronique'],
            ['code' => 'C017', 'libelle' => 'Insuffisance rénale', 'description' => 'Maladie rénale chronique'],
            ['code' => 'C018', 'libelle' => 'Accident domestique', 'description' => 'Chute, brûlure, électrocution'],
            ['code' => 'C019', 'libelle' => 'Incendie', 'description' => 'Brûlure mortelle'],
            ['code' => 'C020', 'libelle' => 'Empoisonnement', 'description' => 'Intoxication alimentaire ou chimique'],
            ['code' => 'C099', 'libelle' => 'Cause inconnue', 'description' => 'Cause non déterminée ou non spécifiée'],
        ];

        foreach ($causes as $cause) {
            CauseDeces::create($cause);
        }

        $this->command->info('21 causes de décès créées avec succès !');
    }
}