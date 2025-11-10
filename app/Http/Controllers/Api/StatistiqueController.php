<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deces;
use App\Models\Naissance;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class StatistiqueController extends Controller
{
    /**
     * Dashboard général avec toutes les statistiques
     * 
     * @return JsonResponse
     */
    public function dashboard(): JsonResponse
    {
        try {
            // Statistiques globales
            $totalDeces = Deces::count();
            $totalNaissances = Naissance::count();
            $totalRegions = Region::count();

            // Décès par année
            $decesParAnnee = Deces::statistiquesParAnnee();

            // Naissances par année
            $naissancesParAnnee = Naissance::selectRaw('ANNEE_NAISSANCE as annee, COUNT(*) as total')
                ->whereNotNull('ANNEE_NAISSANCE')
                ->groupBy('ANNEE_NAISSANCE')
                ->orderBy('ANNEE_NAISSANCE', 'desc')
                ->get();

            // Décès par sexe
            $decesParSexe = Deces::statistiquesParSexe();

            // Naissances par sexe
            $naissancesParSexe = Naissance::selectRaw('SEXE_ENFANT, COUNT(*) as total')
                ->whereNotNull('SEXE_ENFANT')
                ->groupBy('SEXE_ENFANT')
                ->get();

            // Causes de décès les plus fréquentes
            $causesPlusFrequentes = Deces::causesPlusFrequentes(5);

            // Pyramide des âges
            $pyramideAges = Deces::pyramideDesAges();

            return response()->json([
                'success' => true,
                'message' => 'Statistiques du dashboard récupérées avec succès',
                'data' => [
                    'totaux' => [
                        'deces' => $totalDeces,
                        'naissances' => $totalNaissances,
                        'regions' => $totalRegions,
                    ],
                    'deces_par_annee' => $decesParAnnee,
                    'naissances_par_annee' => $naissancesParAnnee,
                    'deces_par_sexe' => $decesParSexe,
                    'naissances_par_sexe' => $naissancesParSexe,
                    'causes_plus_frequentes' => $causesPlusFrequentes,
                    'pyramide_ages' => $pyramideAges,
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

    /**
     * Décès par année
     * 
     * @return JsonResponse
     */
    public function decesParAnnee(): JsonResponse
    {
        try {
            $stats = Deces::statistiquesParAnnee();

            return response()->json([
                'success' => true,
                'message' => 'Statistiques des décès par année',
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Naissances par année
     * 
     * @return JsonResponse
     */
    public function naissancesParAnnee(): JsonResponse
    {
        try {
            $stats = Naissance::selectRaw('ANNEE_NAISSANCE as annee, COUNT(*) as total')
                ->whereNotNull('ANNEE_NAISSANCE')
                ->groupBy('ANNEE_NAISSANCE')
                ->orderBy('ANNEE_NAISSANCE', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Statistiques des naissances par année',
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pyramide des âges
     * 
     * @return JsonResponse
     */
    public function pyramideAges(): JsonResponse
    {
        try {
            $pyramide = Deces::pyramideDesAges();

            return response()->json([
                'success' => true,
                'message' => 'Pyramide des âges récupérée',
                'data' => $pyramide
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la pyramide des âges',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Causes de décès les plus fréquentes
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function causesDeces(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 10);
            $causes = Deces::causesPlusFrequentes($limit);

            return response()->json([
                'success' => true,
                'message' => 'Causes de décès les plus fréquentes',
                'data' => $causes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des causes de décès',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Taux de natalité par région
     * 
     * @return JsonResponse
     */
    public function tauxNatalite(): JsonResponse
    {
        try {
            $stats = Naissance::selectRaw('region_id, COUNT(*) as total')
                ->with(['region'])
                ->whereNotNull('region_id')
                ->groupBy('region_id')
                ->orderBy('total', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Taux de natalité par région',
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du taux de natalité',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Taux de mortalité par région
     * 
     * @return JsonResponse
     */
    public function tauxMortalite(): JsonResponse
    {
        try {
            $stats = Deces::selectRaw('region_id, COUNT(*) as total')
                ->with(['region'])
                ->whereNotNull('region_id')
                ->groupBy('region_id')
                ->orderBy('total', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Taux de mortalité par région',
                'data' => $stats
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du taux de mortalité',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}