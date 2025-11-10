<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Deces;
use App\Models\Region;
use App\Models\District;
use App\Models\Commune;
use App\Models\CauseDeces;
use App\Models\Profession;
use App\Models\Nationalite;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DecesControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $region;
    protected $district;
    protected $commune;
    protected $causeDeces;
    protected $profession;
    protected $nationalite;
    protected $deces;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Créer des données de test
        $this->region = Region::factory()->create();
        $this->district = District::factory()->create(['region_id' => $this->region->id]);
        $this->commune = Commune::factory()->create(['district_id' => $this->district->id]);
        $this->causeDeces = CauseDeces::factory()->create();
        $this->profession = Profession::factory()->create();
        $this->nationalite = Nationalite::factory()->create();
        
        $this->deces = Deces::factory()->create([
            'region_id' => $this->region->id,
            'district_id' => $this->district->id,
            'commune_id' => $this->commune->id,
            'cause_deces_id' => $this->causeDeces->id,
            'profession_defunt_id' => $this->profession->id,
            'profession_declarant_id' => $this->profession->id,
            'nationalite_id' => $this->nationalite->id,
            'ANNEE_DECES' => 2023,
            'MOIS_DECES' => 6,
            'JOUR_DECES' => 15,
            'SEXE_DEFUNT' => 1,
            'N_ACTE' => 1001,
        ]);
    }

    /** @test */
    public function liste_des_deces_avec_succes()
    {
        $response = $this->getJson('/api/deces');
        
        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'current_page',
                        'data' => [
                            '*' => [
                                'id',
                                'ANNEE_DECES',
                                'SEXE_DEFUNT',
                                'region',
                                'district',
                                'commune',
                                'causeDeces'
                            ]
                        ]
                    ]
                ]);
    }

    /** @test */
    public function liste_des_deces_avec_filtre_annee()
    {
        $response = $this->getJson('/api/deces?annee=2023');
        
        $response->assertStatus(200)
                ->assertJsonPath('success', true);
    }

    /** @test */
    public function liste_des_deces_avec_filtre_region()
    {
        $response = $this->getJson("/api/deces?region_id={$this->region->id}");
        
        $response->assertStatus(200)
                ->assertJsonPath('success', true);
    }

    /** @test */
    public function liste_des_deces_avec_filtre_sexe()
    {
        $response = $this->getJson('/api/deces?sexe=1');
        
        $response->assertStatus(200)
                ->assertJsonPath('success', true);
    }

    /** @test */
    public function creer_un_deces_avec_succes()
    {
        $data = [
            'region_id' => $this->region->id,
            'district_id' => $this->district->id,
            'commune_id' => $this->commune->id,
            'cause_deces_id' => $this->causeDeces->id,
            'profession_defunt_id' => $this->profession->id,
            'profession_declarant_id' => $this->profession->id,
            'nationalite_id' => $this->nationalite->id,
            'ANNEE_DECES' => 2024,
            'MOIS_DECES' => 6,
            'JOUR_DECES' => 15,
            'SEXE_DEFUNT' => 2,
            'N_ACTE' => 12345,
        ];

        $response = $this->postJson('/api/deces', $data);
        
        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Décès enregistré avec succès'
                ]);

        $this->assertDatabaseHas('deces', ['N_ACTE' => 12345]);
    }

    /** @test */
    public function validation_erreur_creation_deces()
    {
        $data = [
            'ANNEE_DECES' => 1800, // Année invalide
            'MOIS_DECES' => 13, // Mois invalide
            'SEXE_DEFUNT' => 3, // Sexe invalide
        ];

        $response = $this->postJson('/api/deces', $data);
        
        $response->assertStatus(422)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'errors'
                ]);
    }

    /** @test */
    public function afficher_un_deces_existant()
    {
        $response = $this->getJson("/api/deces/{$this->deces->id}");
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $this->deces->id
                    ]
                ]);
    }

    /** @test */
    public function erreur_afficher_deces_inexistant()
    {
        $response = $this->getJson('/api/deces/9999');
        
        $response->assertStatus(404)
                ->assertJson([
                    'success' => false
                ]);
    }

    /** @test */
    public function mettre_a_jour_un_deces()
    {
        $data = [
            'ANNEE_DECES' => 2024,
            'SEXE_DEFUNT' => 2,
        ];

        $response = $this->putJson("/api/deces/{$this->deces->id}", $data);
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Décès mis à jour avec succès'
                ]);

        $this->assertDatabaseHas('deces', [
            'id' => $this->deces->id,
            'ANNEE_DECES' => 2024,
            'SEXE_DEFUNT' => 2
        ]);
    }

    /** @test */
    public function supprimer_un_deces()
    {
        $response = $this->deleteJson("/api/deces/{$this->deces->id}");
        
        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Décès supprimé avec succès'
                ]);

        $this->assertDatabaseMissing('deces', ['id' => $this->deces->id]);
    }
}