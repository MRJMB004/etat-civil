<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deces;
use App\Models\Naissance;
use App\Models\Region;
use App\Models\District;
use App\Models\Commune;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class StatistiqueController extends Controller
{
    /**
     * Dashboard général avec toutes les statistiques avancées
     */
    public function dashboard(Request $request): JsonResponse
    {
        try {
            // 🎛️ Filtres optionnels
            $annee = $request->get('annee');
            $regionId = $request->get('region_id');
            $districtId = $request->get('district_id');

            // ========== REQUÊTES DE BASE AVEC FILTRES ==========
            $queryDeces = Deces::query();
            $queryNaissances = Naissance::query();

            if ($annee) {
                $queryDeces->where('ANNEE_DECES', $annee);
                $queryNaissances->where('ANNEE_NAISSANCE', $annee);
            }
            if ($regionId) {
                $queryDeces->where('region_id', $regionId);
                $queryNaissances->where('region_id', $regionId);
            }
            if ($districtId) {
                $queryDeces->where('district_id', $districtId);
                $queryNaissances->where('district_id', $districtId);
            }

            // ========== STATISTIQUES GLOBALES ==========
            $totalDeces = $queryDeces->count();
            $totalNaissances = $queryNaissances->count();
            $totalRegions = Region::count();
            $totalDistricts = District::count();
            $totalCommunes = Commune::count();

            // ========== ÉVOLUTION TEMPORELLE ==========
            $decesParAnnee = Deces::statistiquesParAnnee();
            $naissancesParAnnee = Naissance::selectRaw('ANNEE_NAISSANCE as annee, COUNT(*) as total')
                ->whereNotNull('ANNEE_NAISSANCE')
                ->groupBy('ANNEE_NAISSANCE')
                ->orderBy('ANNEE_NAISSANCE', 'desc')
                ->get();

            // ========== RÉPARTITION DÉMOGRAPHIQUE ==========
            $decesParSexe = Deces::statistiquesParSexe($annee);
            $naissancesParSexe = Naissance::selectRaw('SEXE_ENFANT, COUNT(*) as total')
                ->whereNotNull('SEXE_ENFANT')
                ->when($annee, function($q) use ($annee) {
                    $q->where('ANNEE_NAISSANCE', $annee);
                })
                ->groupBy('SEXE_ENFANT')
                ->get();

            // ========== INDICATEURS DE SANTÉ ==========
            $tauxAssistanceNaissance = Naissance::selectRaw('NAISS_ASSIS_PERS_SANTE, COUNT(*) as total')
                ->whereNotNull('NAISS_ASSIS_PERS_SANTE')
                ->when($annee, function($q) use ($annee) {
                    $q->where('ANNEE_NAISSANCE', $annee);
                })
                ->groupBy('NAISS_ASSIS_PERS_SANTE')
                ->get();

            $decesHopital = (clone $queryDeces)->where('SANITAIRE', 1)->count();
            $decesDomicile = (clone $queryDeces)->where('SANITAIRE', 2)->count();

            // ========== STATISTIQUES GÉOGRAPHIQUES ==========
            $decesParRegion = Deces::selectRaw('region_id, COUNT(*) as total')
                ->with(['region'])
                ->whereNotNull('region_id')
                ->when($annee, function($q) use ($annee) {
                    $q->where('ANNEE_DECES', $annee);
                })
                ->groupBy('region_id')
                ->orderBy('total', 'desc')
                ->limit(10)
                ->get();

            $naissancesParRegion = Naissance::selectRaw('region_id, COUNT(*) as total')
                ->with(['region'])
                ->whereNotNull('region_id')
                ->when($annee, function($q) use ($annee) {
                    $q->where('ANNEE_NAISSANCE', $annee);
                })
                ->groupBy('region_id')
                ->orderBy('total', 'desc')
                ->limit(10)
                ->get();

            // ========== INDICATEURS AVANCÉS ==========
            $causesPlusFrequentes = Deces::causesPlusFrequentes(8);
            $pyramideAges = Deces::pyramideDesAges();

            // ========== MILIEU URBAIN/RURAL ==========
            $naissancesParMilieu = Naissance::selectRaw('MILIEU, COUNT(*) as total')
                ->whereNotNull('MILIEU')
                ->when($annee, function($q) use ($annee) {
                    $q->where('ANNEE_NAISSANCE', $annee);
                })
                ->groupBy('MILIEU')
                ->get();

            $decesParMilieu = Deces::selectRaw('MILIEU, COUNT(*) as total')
                ->whereNotNull('MILIEU')
                ->when($annee, function($q) use ($annee) {
                    $q->where('ANNEE_DECES', $annee);
                })
                ->groupBy('MILIEU')
                ->get();

            // ========== CALCUL DES TAUX ==========
            $tauxMortalite = $totalDeces > 0 ? round(($totalDeces / ($totalDeces + $totalNaissances)) * 100, 2) : 0;
            $tauxNatalite = $totalNaissances > 0 ? round(($totalNaissances / ($totalDeces + $totalNaissances)) * 100, 2) : 0;

            return response()->json([
                'success' => true,
                'message' => 'Dashboard statistique récupéré avec succès',
                'data' => [
                    'filtres_appliques' => [
                        'annee' => $annee,
                        'region_id' => $regionId,
                        'district_id' => $districtId
                    ],
                    'kpis' => [
                        'total_deces' => $totalDeces,
                        'total_naissances' => $totalNaissances,
                        'total_regions' => $totalRegions,
                        'total_districts' => $totalDistricts,
                        'total_communes' => $totalCommunes,
                        'taux_mortalite' => $tauxMortalite,
                        'taux_natalite' => $tauxNatalite,
                        'ratio_naissance_deces' => $totalDeces > 0 ? round($totalNaissances / $totalDeces, 2) : 0,
                    ],
                    'evolution_temps' => [
                        'deces_par_annee' => $decesParAnnee,
                        'naissances_par_annee' => $naissancesParAnnee,
                    ],
                    'repartition_demographique' => [
                        'deces_par_sexe' => $decesParSexe,
                        'naissances_par_sexe' => $naissancesParSexe,
                        'pyramide_ages' => $pyramideAges,
                    ],
                    'indicateurs_sante' => [
                        'assistance_naissance' => $tauxAssistanceNaissance,
                        'deces_hopital' => $decesHopital,
                        'deces_domicile' => $decesDomicile,
                        'taux_assistance_medicale' => $tauxAssistanceNaissance->sum('total') > 0 ? 
                            round(($tauxAssistanceNaissance->where('NAISS_ASSIS_PERS_SANTE', 1)->first()->total ?? 0) / $tauxAssistanceNaissance->sum('total') * 100, 2) : 0,
                    ],
                    'repartition_geographique' => [
                        'deces_par_region' => $decesParRegion,
                        'naissances_par_region' => $naissancesParRegion,
                    ],
                    'causes_medicales' => [
                        'causes_deces_frequentes' => $causesPlusFrequentes,
                    ],
                    'repartition_milieu' => [
                        'naissances_urbain_rural' => $naissancesParMilieu,
                        'deces_urbain_rural' => $decesParMilieu,
                    ],
                    'tendances' => $this->calculerTendances($decesParAnnee, $naissancesParAnnee),
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur dashboard statistiques', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du dashboard',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * Décès par année avec filtres avancés
     */
    public function decesParAnnee(Request $request): JsonResponse
    {
        try {
            $regionId = $request->get('region_id');
            $districtId = $request->get('district_id');
            $sexe = $request->get('sexe');

            $query = Deces::selectRaw('ANNEE_DECES as annee, COUNT(*) as total')
                ->whereNotNull('ANNEE_DECES')
                ->when($regionId, function($q) use ($regionId) {
                    $q->where('region_id', $regionId);
                })
                ->when($districtId, function($q) use ($districtId) {
                    $q->where('district_id', $districtId);
                })
                ->when($sexe, function($q) use ($sexe) {
                    $q->where('SEXE_DEFUNT', $sexe);
                })
                ->groupBy('ANNEE_DECES')
                ->orderBy('ANNEE_DECES', 'desc');

            $stats = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Statistiques des décès par année',
                'data' => $stats,
                'filtres' => [
                    'region_id' => $regionId,
                    'district_id' => $districtId,
                    'sexe' => $sexe
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur décès par année', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * Naissances par année avec filtres avancés
     */
    public function naissancesParAnnee(Request $request): JsonResponse
    {
        try {
            $regionId = $request->get('region_id');
            $districtId = $request->get('district_id');
            $sexe = $request->get('sexe');
            $milieu = $request->get('milieu');

            $query = Naissance::selectRaw('ANNEE_NAISSANCE as annee, COUNT(*) as total')
                ->whereNotNull('ANNEE_NAISSANCE')
                ->when($regionId, function($q) use ($regionId) {
                    $q->where('region_id', $regionId);
                })
                ->when($districtId, function($q) use ($districtId) {
                    $q->where('district_id', $districtId);
                })
                ->when($sexe, function($q) use ($sexe) {
                    $q->where('SEXE_ENFANT', $sexe);
                })
                ->when($milieu, function($q) use ($milieu) {
                    $q->where('MILIEU', $milieu);
                })
                ->groupBy('ANNEE_NAISSANCE')
                ->orderBy('ANNEE_NAISSANCE', 'desc');

            $stats = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Statistiques des naissances par année',
                'data' => $stats,
                'filtres' => [
                    'region_id' => $regionId,
                    'district_id' => $districtId,
                    'sexe' => $sexe,
                    'milieu' => $milieu
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur naissances par année', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * Pyramide des âges avancée
     */
    public function pyramideAges(Request $request): JsonResponse
    {
        try {
            $annee = $request->get('annee');
            $regionId = $request->get('region_id');

            $query = Deces::selectRaw('
                    CASE 
                        WHEN (ANNEE_DECES - ANNEE_NAISSANCE_DEFUNT) < 1 THEN "0-1 an"
                        WHEN (ANNEE_DECES - ANNEE_NAISSANCE_DEFUNT) BETWEEN 1 AND 4 THEN "1-4 ans"
                        WHEN (ANNEE_DECES - ANNEE_NAISSANCE_DEFUNT) BETWEEN 5 AND 14 THEN "5-14 ans"
                        WHEN (ANNEE_DECES - ANNEE_NAISSANCE_DEFUNT) BETWEEN 15 AND 24 THEN "15-24 ans"
                        WHEN (ANNEE_DECES - ANNEE_NAISSANCE_DEFUNT) BETWEEN 25 AND 34 THEN "25-34 ans"
                        WHEN (ANNEE_DECES - ANNEE_NAISSANCE_DEFUNT) BETWEEN 35 AND 44 THEN "35-44 ans"
                        WHEN (ANNEE_DECES - ANNEE_NAISSANCE_DEFUNT) BETWEEN 45 AND 54 THEN "45-54 ans"
                        WHEN (ANNEE_DECES - ANNEE_NAISSANCE_DEFUNT) BETWEEN 55 AND 64 THEN "55-64 ans"
                        WHEN (ANNEE_DECES - ANNEE_NAISSANCE_DEFUNT) >= 65 THEN "65+ ans"
                        ELSE "Non défini"
                    END as tranche_age,
                    SEXE_DEFUNT,
                    COUNT(*) as total
                ')
                ->whereNotNull('ANNEE_DECES')
                ->whereNotNull('ANNEE_NAISSANCE_DEFUNT')
                ->whereNotNull('SEXE_DEFUNT')
                ->when($annee, function($q) use ($annee) {
                    $q->where('ANNEE_DECES', $annee);
                })
                ->when($regionId, function($q) use ($regionId) {
                    $q->where('region_id', $regionId);
                })
                ->groupBy('tranche_age', 'SEXE_DEFUNT')
                ->orderByRaw('MIN(ANNEE_DECES - ANNEE_NAISSANCE_DEFUNT)');

            $pyramide = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Pyramide des âges récupérée',
                'data' => $pyramide,
                'filtres' => [
                    'annee' => $annee,
                    'region_id' => $regionId
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur pyramide des âges', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération de la pyramide des âges',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * Causes de décès les plus fréquentes avec filtres
     */
    public function causesDeces(Request $request): JsonResponse
    {
        try {
            $limit = $request->get('limit', 10);
            $annee = $request->get('annee');
            $regionId = $request->get('region_id');

            $query = Deces::selectRaw('LIB_CAUSE_DECES, COUNT(*) as total')
                ->whereNotNull('LIB_CAUSE_DECES')
                ->when($annee, function($q) use ($annee) {
                    $q->where('ANNEE_DECES', $annee);
                })
                ->when($regionId, function($q) use ($regionId) {
                    $q->where('region_id', $regionId);
                })
                ->groupBy('LIB_CAUSE_DECES')
                ->orderBy('total', 'desc')
                ->limit($limit);

            $causes = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Causes de décès les plus fréquentes',
                'data' => $causes,
                'filtres' => [
                    'limit' => $limit,
                    'annee' => $annee,
                    'region_id' => $regionId
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur causes décès', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des causes de décès',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * Taux de natalité par région avec indicateurs avancés
     */
    public function tauxNatalite(Request $request): JsonResponse
    {
        try {
            $annee = $request->get('annee');

            $stats = Naissance::selectRaw('
                    region_id, 
                    COUNT(*) as total_naissances,
                    AVG(AGE_MERE) as age_moyen_mere,
                    AVG(AGE_PERE) as age_moyen_pere,
                    SUM(CASE WHEN SEXE_ENFANT = 1 THEN 1 ELSE 0 END) as garcons,
                    SUM(CASE WHEN SEXE_ENFANT = 2 THEN 1 ELSE 0 END) as filles,
                    SUM(CASE WHEN NAISS_ASSIS_PERS_SANTE = 1 THEN 1 ELSE 0 END) as assistes_medicalement
                ')
                ->with(['region'])
                ->whereNotNull('region_id')
                ->when($annee, function($q) use ($annee) {
                    $q->where('ANNEE_NAISSANCE', $annee);
                })
                ->groupBy('region_id')
                ->orderBy('total_naissances', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Taux de natalité par région',
                'data' => $stats,
                'filtres' => ['annee' => $annee]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur taux natalité', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du taux de natalité',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * Taux de mortalité par région avec indicateurs avancés
     */
    public function tauxMortalite(Request $request): JsonResponse
    {
        try {
            $annee = $request->get('annee');

            $stats = Deces::selectRaw('
                    region_id, 
                    COUNT(*) as total_deces,
                    AVG(ANNEE_DECES - ANNEE_NAISSANCE_DEFUNT) as age_moyen_deces,
                    SUM(CASE WHEN SEXE_DEFUNT = 1 THEN 1 ELSE 0 END) as hommes,
                    SUM(CASE WHEN SEXE_DEFUNT = 2 THEN 1 ELSE 0 END) as femmes,
                    SUM(CASE WHEN SANITAIRE = 1 THEN 1 ELSE 0 END) as hopital,
                    SUM(CASE WHEN SANITAIRE = 2 THEN 1 ELSE 0 END) as domicile
                ')
                ->with(['region'])
                ->whereNotNull('region_id')
                ->whereNotNull('ANNEE_DECES')
                ->whereNotNull('ANNEE_NAISSANCE_DEFUNT')
                ->when($annee, function($q) use ($annee) {
                    $q->where('ANNEE_DECES', $annee);
                })
                ->groupBy('region_id')
                ->orderBy('total_deces', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Taux de mortalité par région',
                'data' => $stats,
                'filtres' => ['annee' => $annee]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur taux mortalité', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du taux de mortalité',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * Statistiques comparatives régions
     */
    public function comparaisonRegions(Request $request): JsonResponse
    {
        try {
            $annee = $request->get('annee', date('Y'));

            $naissances = Naissance::selectRaw('region_id, COUNT(*) as naissances')
                ->whereNotNull('region_id')
                ->where('ANNEE_NAISSANCE', $annee)
                ->groupBy('region_id')
                ->get()
                ->keyBy('region_id');

            $deces = Deces::selectRaw('region_id, COUNT(*) as deces')
                ->whereNotNull('region_id')
                ->where('ANNEE_DECES', $annee)
                ->groupBy('region_id')
                ->get()
                ->keyBy('region_id');

            $regions = Region::with(['districts'])->get()->map(function($region) use ($naissances, $deces, $annee) {
                $naissancesRegion = $naissances->get($region->id);
                $decesRegion = $deces->get($region->id);

                return [
                    'id' => $region->id,
                    'libelle' => $region->libelle,
                    'code' => $region->code,
                    'naissances' => $naissancesRegion ? $naissancesRegion->naissances : 0,
                    'deces' => $decesRegion ? $decesRegion->deces : 0,
                    'solde_naturel' => ($naissancesRegion ? $naissancesRegion->naissances : 0) - ($decesRegion ? $decesRegion->deces : 0),
                    'taux_accroissement' => $decesRegion && $decesRegion->deces > 0 ? 
                        round((($naissancesRegion ? $naissancesRegion->naissances : 0) / $decesRegion->deces) * 100, 2) : 0,
                    'nombre_districts' => $region->districts->count(),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Comparaison des régions',
                'data' => $regions,
                'filtres' => ['annee' => $annee]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur comparaison régions', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la comparaison des régions',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * Calculer les tendances évolutives
     */
    private function calculerTendances($decesParAnnee, $naissancesParAnnee)
    {
        $decesRecent = $decesParAnnee->take(2);
        $naissancesRecent = $naissancesParAnnee->take(2);

        $tendanceDeces = $decesRecent->count() === 2 ? 
            round((($decesRecent[0]->total - $decesRecent[1]->total) / $decesRecent[1]->total) * 100, 2) : 0;

        $tendanceNaissances = $naissancesRecent->count() === 2 ? 
            round((($naissancesRecent[0]->total - $naissancesRecent[1]->total) / $naissancesRecent[1]->total) * 100, 2) : 0;

        return [
            'tendance_deces' => $tendanceDeces,
            'tendance_naissances' => $tendanceNaissances,
            'interpretation' => $this->interpreterTendances($tendanceDeces, $tendanceNaissances)
        ];
    }

    /**
     * Interpréter les tendances
     */
    private function interpreterTendances($tendanceDeces, $tendanceNaissances)
    {
        if ($tendanceNaissances > 5 && $tendanceDeces < -5) {
            return "Forte croissance démographique positive";
        } elseif ($tendanceNaissances < -5 && $tendanceDeces > 5) {
            return "Déclin démographique préoccupant";
        } elseif ($tendanceNaissances > 0 && $tendanceDeces < 0) {
            return "Croissance démographique modérée";
        } else {
            return "Stabilité démographique relative";
        }
    }
}