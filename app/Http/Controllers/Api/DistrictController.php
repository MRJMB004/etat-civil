<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Deces;
use App\Models\Naissance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DistrictController extends Controller
{
    /**
     * Liste tous les districts
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = District::with(['region']);

            // Filtre par région
            if ($request->has('region_id')) {
                $query->where('region_id', $request->region_id);
            }

            $districts = $query->orderBy('libelle')->get();

            return response()->json([
                'success' => true,
                'message' => 'Liste des districts récupérée avec succès',
                'data' => $districts
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des districts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Afficher un district spécifique
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $district = District::with(['region', 'communes'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'District récupéré avec succès',
                'data' => $district
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'District non trouvé',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Obtenir les communes d'un district
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function communes(int $id): JsonResponse
    {
        try {
            $district = District::findOrFail($id);
            $communes = $district->communes()->orderBy('libelle')->get();

            return response()->json([
                'success' => true,
                'message' => 'Communes récupérées avec succès',
                'data' => $communes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des communes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques d'un district
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function statistiques(int $id): JsonResponse
    {
        try {
            $district = District::with(['region'])->findOrFail($id);

            // Statistiques des décès
            $totalDeces = Deces::where('district_id', $id)->count();
            $decesParSexe = Deces::where('district_id', $id)
                ->selectRaw('SEXE_DEFUNT, COUNT(*) as total')
                ->whereNotNull('SEXE_DEFUNT')
                ->groupBy('SEXE_DEFUNT')
                ->get();

            // Statistiques des naissances
            $totalNaissances = Naissance::where('district_id', $id)->count();
            $naissancesParSexe = Naissance::where('district_id', $id)
                ->selectRaw('SEXE_ENFANT, COUNT(*) as total')
                ->whereNotNull('SEXE_ENFANT')
                ->groupBy('SEXE_ENFANT')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Statistiques récupérées avec succès',
                'data' => [
                    'district' => $district,
                    'deces' => [
                        'total' => $totalDeces,
                        'par_sexe' => $decesParSexe,
                    ],
                    'naissances' => [
                        'total' => $totalNaissances,
                        'par_sexe' => $naissancesParSexe,
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}