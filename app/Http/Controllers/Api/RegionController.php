<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\Deces;
use App\Models\Naissance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RegionController extends Controller
{
    /**
     * Liste toutes les régions
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $regions = Region::orderBy('libelle')->get();

            return response()->json([
                'success' => true,
                'message' => 'Liste des régions récupérée avec succès',
                'data' => $regions
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la récupération des régions', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des régions',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * Afficher une région spécifique
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $region = Region::with(['districts'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Région récupérée avec succès',
                'data' => $region
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Région non trouvée', [
                'error' => $e->getMessage(),
                'region_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Région non trouvée',
                'error' => 'La région demandée n\'existe pas'
            ], 404);
        }
    }

    /**
     * Obtenir les districts d'une région
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function districts(int $id): JsonResponse
    {
        try {
            $region = Region::findOrFail($id);
            $districts = $region->districts()->orderBy('libelle')->get();

            return response()->json([
                'success' => true,
                'message' => 'Districts récupérés avec succès',
                'data' => $districts
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la récupération des districts', [
                'error' => $e->getMessage(),
                'region_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des districts',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques d'une région
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function statistiques(int $id): JsonResponse
    {
        try {
            $region = Region::findOrFail($id);

            // Statistiques des décès
            $totalDeces = Deces::where('region_id', $id)->count();
            $decesParSexe = Deces::where('region_id', $id)
                ->selectRaw('SEXE_DEFUNT, COUNT(*) as total')
                ->whereNotNull('SEXE_DEFUNT')
                ->groupBy('SEXE_DEFUNT')
                ->get();

            $decesParAnnee = Deces::where('region_id', $id)
                ->selectRaw('ANNEE_DECES as annee, COUNT(*) as total')
                ->whereNotNull('ANNEE_DECES')
                ->groupBy('ANNEE_DECES')
                ->orderBy('ANNEE_DECES', 'desc')
                ->get();

            // Statistiques des naissances
            $totalNaissances = Naissance::where('region_id', $id)->count();
            $naissancesParSexe = Naissance::where('region_id', $id)
                ->selectRaw('SEXE_ENFANT, COUNT(*) as total')
                ->whereNotNull('SEXE_ENFANT')
                ->groupBy('SEXE_ENFANT')
                ->get();

            $naissancesParAnnee = Naissance::where('region_id', $id)
                ->selectRaw('ANNEE_NAISSANCE as annee, COUNT(*) as total')
                ->whereNotNull('ANNEE_NAISSANCE')
                ->groupBy('ANNEE_NAISSANCE')
                ->orderBy('ANNEE_NAISSANCE', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Statistiques récupérées avec succès',
                'data' => [
                    'region' => $region,
                    'deces' => [
                        'total' => $totalDeces,
                        'par_sexe' => $decesParSexe,
                        'par_annee' => $decesParAnnee,
                    ],
                    'naissances' => [
                        'total' => $totalNaissances,
                        'par_sexe' => $naissancesParSexe,
                        'par_annee' => $naissancesParAnnee,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la récupération des statistiques', [
                'error' => $e->getMessage(),
                'region_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }
}