<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\Deces;
use App\Models\Naissance;
use App\Models\District;
use App\Models\Commune;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RegionController extends Controller
{
    /**
     * Liste toutes les régions avec filtres avancés
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Region::with(['districts']);

            // ========== FILTRES AVANCÉS ==========
            
            // 🔍 Recherche par nom
            if ($request->filled('search')) {
                $query->where(function($q) use ($request) {
                    $q->where('libelle', 'LIKE', "%{$request->search}%")
                      ->orWhere('code', 'LIKE', "%{$request->search}%");
                });
            }

            // 📊 Inclure les statistiques si demandé
            if ($request->filled('avec_stats')) {
                $query->withCount(['deces', 'naissances', 'districts']);
            }

            // ========== TRI ==========
            $sortBy = $request->get('sort_by', 'libelle');
            $sortOrder = $request->get('sort_order', 'asc');
            
            $allowedSortColumns = ['libelle', 'code', 'created_at', 'deces_count', 'naissances_count', 'districts_count'];
            if (in_array($sortBy, $allowedSortColumns)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('libelle', 'asc');
            }

            // ========== PAGINATION ==========
            if ($request->filled('paginate')) {
                $perPage = min($request->get('per_page', 20), 100);
                $regions = $query->paginate($perPage);

                $pagination = [
                    'total' => $regions->total(),
                    'par_page' => $regions->perPage(),
                    'page_courante' => $regions->currentPage(),
                    'derniere_page' => $regions->lastPage(),
                ];
            } else {
                $regions = $query->get();
                $pagination = null;
            }

            // 📈 Statistiques globales si pas de pagination
            $statsGlobales = null;
            if (!$request->filled('paginate')) {
                $statsGlobales = [
                    'total_regions' => $regions->count(),
                    'total_districts' => District::count(),
                    'total_communes' => Commune::count(),
                    'total_deces' => Deces::count(),
                    'total_naissances' => Naissance::count(),
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Liste des régions récupérée avec succès',
                'data' => $regions,
                'pagination' => $pagination,
                'statistiques_globales' => $statsGlobales,
                'filtres_appliques' => $request->except(['page', 'per_page', 'sort_by', 'sort_order'])
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur récupération régions', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des régions',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * Afficher une région spécifique avec données complètes
     */
    public function show(int $id): JsonResponse
    {
        try {
            $region = Region::with([
                'districts' => function($query) {
                    $query->orderBy('libelle')->withCount(['deces', 'naissances', 'communes']);
                }
            ])->findOrFail($id);

            // 📊 Statistiques rapides
            $statsRapides = [
                'nombre_districts' => $region->districts->count(),
                'total_deces' => $region->deces()->count(),
                'total_naissances' => $region->naissances()->count(),
                'total_communes' => $region->districts->sum('communes_count'),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Région récupérée avec succès',
                'data' => [
                    'region' => $region,
                    'statistiques_rapides' => $statsRapides
                ]
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
     * Obtenir les districts d'une région avec statistiques
     */
    public function districts(int $id, Request $request): JsonResponse
    {
        try {
            $region = Region::findOrFail($id);
            
            $query = $region->districts()->withCount(['deces', 'naissances', 'communes']);

            // 🔍 Recherche dans les districts
            if ($request->filled('search')) {
                $query->where(function($q) use ($request) {
                    $q->where('libelle', 'LIKE', "%{$request->search}%")
                      ->orWhere('code', 'LIKE', "%{$request->search}%");
                });
            }

            // 📊 Tri par statistiques
            $sortBy = $request->get('sort_by', 'libelle');
            $sortOrder = $request->get('sort_order', 'asc');
            
            if (in_array($sortBy, ['deces_count', 'naissances_count', 'communes_count'])) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('libelle', $sortOrder);
            }

            $districts = $query->get();

            // 📈 Statistiques de regroupement
            $statsRegroupement = [
                'total_districts' => $districts->count(),
                'total_communes' => $districts->sum('communes_count'),
                'total_deces' => $districts->sum('deces_count'),
                'total_naissances' => $districts->sum('naissances_count'),
                'district_plus_peuple' => $districts->sortByDesc('deces_count')->first(),
                'district_moins_peuple' => $districts->sortBy('deces_count')->first(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Districts récupérés avec succès',
                'data' => [
                    'region' => $region->only(['id', 'libelle', 'code']),
                    'districts' => $districts,
                    'statistiques_regroupement' => $statsRegroupement
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur récupération districts', [
                'error' => $e->getMessage(),
                'region_id' => $id,
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des districts',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques détaillées d'une région
     */
    public function statistiques(int $id, Request $request): JsonResponse
    {
        try {
            $region = Region::findOrFail($id);
            $annee = $request->get('annee');

            // ========== REQUÊTES DE BASE ==========
            $queryDeces = Deces::where('region_id', $id);
            $queryNaissances = Naissance::where('region_id', $id);

            if ($annee) {
                $queryDeces->where('ANNEE_DECES', $annee);
                $queryNaissances->where('ANNEE_NAISSANCE', $annee);
            }

            // ========== STATISTIQUES GÉNÉRALES ==========
            $totalDeces = $queryDeces->count();
            $totalNaissances = $queryNaissances->count();

            // ========== ANALYSE DÉCÈS ==========
            $decesParSexe = $queryDeces->selectRaw('SEXE_DEFUNT, COUNT(*) as total')
                ->whereNotNull('SEXE_DEFUNT')
                ->groupBy('SEXE_DEFUNT')
                ->get();

            $decesParAnnee = Deces::where('region_id', $id)
                ->selectRaw('ANNEE_DECES as annee, COUNT(*) as total')
                ->whereNotNull('ANNEE_DECES')
                ->groupBy('ANNEE_DECES')
                ->orderBy('ANNEE_DECES', 'desc')
                ->limit(10)
                ->get();

            $causesDeces = Deces::where('region_id', $id)
                ->selectRaw('LIB_CAUSE_DECES, COUNT(*) as total')
                ->whereNotNull('LIB_CAUSE_DECES')
                ->when($annee, function($q) use ($annee) {
                    $q->where('ANNEE_DECES', $annee);
                })
                ->groupBy('LIB_CAUSE_DECES')
                ->orderBy('total', 'desc')
                ->limit(10)
                ->get();

            $decesParMois = Deces::where('region_id', $id)
                ->selectRaw('MOIS_DECES, COUNT(*) as total')
                ->whereNotNull('MOIS_DECES')
                ->when($annee, function($q) use ($annee) {
                    $q->where('ANNEE_DECES', $annee);
                })
                ->groupBy('MOIS_DECES')
                ->orderBy('MOIS_DECES')
                ->get();

            // ========== ANALYSE NAISSANCES ==========
            $naissancesParSexe = $queryNaissances->selectRaw('SEXE_ENFANT, COUNT(*) as total')
                ->whereNotNull('SEXE_ENFANT')
                ->groupBy('SEXE_ENFANT')
                ->get();

            $naissancesParAnnee = Naissance::where('region_id', $id)
                ->selectRaw('ANNEE_NAISSANCE as annee, COUNT(*) as total')
                ->whereNotNull('ANNEE_NAISSANCE')
                ->groupBy('ANNEE_NAISSANCE')
                ->orderBy('ANNEE_NAISSANCE', 'desc')
                ->limit(10)
                ->get();

            $naissancesParMois = Naissance::where('region_id', $id)
                ->selectRaw('MOIS_NAISSANCE, COUNT(*) as total')
                ->whereNotNull('MOIS_NAISSANCE')
                ->when($annee, function($q) use ($annee) {
                    $q->where('ANNEE_NAISSANCE', $annee);
                })
                ->groupBy('MOIS_NAISSANCE')
                ->orderBy('MOIS_NAISSANCE')
                ->get();

            $assistanceMedicale = Naissance::where('region_id', $id)
                ->selectRaw('NAISS_ASSIS_PERS_SANTE, COUNT(*) as total')
                ->whereNotNull('NAISS_ASSIS_PERS_SANTE')
                ->when($annee, function($q) use ($annee) {
                    $q->where('ANNEE_NAISSANCE', $annee);
                })
                ->groupBy('NAISS_ASSIS_PERS_SANTE')
                ->get();

            // ========== ANALYSE DÉMOGRAPHIQUE ==========
            $pyramideAges = Deces::where('region_id', $id)
                ->selectRaw('
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
                ->groupBy('tranche_age', 'SEXE_DEFUNT')
                ->orderByRaw('MIN(ANNEE_DECES - ANNEE_NAISSANCE_DEFUNT)')
                ->get();

            // ========== ANALYSE GÉOGRAPHIQUE ==========
            $decesParDistrict = Deces::where('region_id', $id)
                ->selectRaw('district_id, COUNT(*) as total')
                ->with(['district'])
                ->whereNotNull('district_id')
                ->when($annee, function($q) use ($annee) {
                    $q->where('ANNEE_DECES', $annee);
                })
                ->groupBy('district_id')
                ->orderBy('total', 'desc')
                ->get();

            $naissancesParDistrict = Naissance::where('region_id', $id)
                ->selectRaw('district_id, COUNT(*) as total')
                ->with(['district'])
                ->whereNotNull('district_id')
                ->when($annee, function($q) use ($annee) {
                    $q->where('ANNEE_NAISSANCE', $annee);
                })
                ->groupBy('district_id')
                ->orderBy('total', 'desc')
                ->get();

            // ========== ANALYSE MILIEU ==========
            $naissancesParMilieu = Naissance::where('region_id', $id)
                ->selectRaw('MILIEU, COUNT(*) as total')
                ->whereNotNull('MILIEU')
                ->when($annee, function($q) use ($annee) {
                    $q->where('ANNEE_NAISSANCE', $annee);
                })
                ->groupBy('MILIEU')
                ->get();

            $decesParMilieu = Deces::where('region_id', $id)
                ->selectRaw('MILIEU, COUNT(*) as total')
                ->whereNotNull('MILIEU')
                ->when($annee, function($q) use ($annee) {
                    $q->where('ANNEE_DECES', $annee);
                })
                ->groupBy('MILIEU')
                ->get();

            // ========== INDICATEURS CALCULÉS ==========
            $soldeNaturel = $totalNaissances - $totalDeces;
            $tauxAccroissement = $totalDeces > 0 ? round(($totalNaissances / $totalDeces) * 100, 2) : 0;
            $tauxAssistance = $assistanceMedicale->sum('total') > 0 ? 
                round(($assistanceMedicale->where('NAISS_ASSIS_PERS_SANTE', 1)->first()->total ?? 0) / $assistanceMedicale->sum('total') * 100, 2) : 0;

            // ========== TENDANCES ==========
            $tendanceDeces = $this->calculerTendance($decesParAnnee);
            $tendanceNaissances = $this->calculerTendance($naissancesParAnnee);

            return response()->json([
                'success' => true,
                'message' => 'Statistiques détaillées de la région récupérées avec succès',
                'data' => [
                    'region' => $region,
                    'filtres' => ['annee' => $annee],
                    'indicateurs_cles' => [
                        'total_deces' => $totalDeces,
                        'total_naissances' => $totalNaissances,
                        'solde_naturel' => $soldeNaturel,
                        'taux_accroissement' => $tauxAccroissement,
                        'taux_assistance_medicale' => $tendanceNaissances,
                        'ratio_naissance_deces' => $totalDeces > 0 ? round($totalNaissances / $totalDeces, 2) : 0,
                        'tendance_deces' => $tendanceDeces,
                        'tendance_naissances' => $tendanceNaissances,
                    ],
                    'analyse_deces' => [
                        'total' => $totalDeces,
                        'par_sexe' => $decesParSexe,
                        'par_annee' => $decesParAnnee,
                        'par_mois' => $decesParMois,
                        'causes_frequentes' => $causesDeces,
                        'par_milieu' => $decesParMilieu,
                    ],
                    'analyse_naissances' => [
                        'total' => $totalNaissances,
                        'par_sexe' => $naissancesParSexe,
                        'par_annee' => $naissancesParAnnee,
                        'par_mois' => $naissancesParMois,
                        'assistance_medicale' => $assistanceMedicale,
                        'par_milieu' => $naissancesParMilieu,
                    ],
                    'analyse_demographique' => [
                        'pyramide_ages' => $pyramideAges,
                        'densite_evenements' => $totalDeces + $totalNaissances,
                    ],
                    'repartition_geographique' => [
                        'deces_par_district' => $decesParDistrict,
                        'naissances_par_district' => $naissancesParDistrict,
                        'district_plus_actif_deces' => $decesParDistrict->first(),
                        'district_plus_actif_naissances' => $naissancesParDistrict->first(),
                    ],
                    'structure_territoriale' => [
                        'nombre_districts' => $region->districts()->count(),
                        'nombre_communes' => $region->districts()->withCount('communes')->get()->sum('communes_count'),
                        'districts_avec_donnees' => $decesParDistrict->count() + $naissancesParDistrict->count(),
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur statistiques région', [
                'error' => $e->getMessage(),
                'region_id' => $id,
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
     * Comparaison de plusieurs régions
     */
    public function comparaison(Request $request): JsonResponse
    {
        try {
            $regionIds = $request->get('region_ids', []);
            $annee = $request->get('annee', date('Y'));

            if (empty($regionIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucune région spécifiée pour la comparaison'
                ], 400);
            }

            $regions = Region::whereIn('id', $regionIds)
                ->get()
                ->map(function($region) use ($annee) {
                    $deces = Deces::where('region_id', $region->id)
                        ->when($annee, function($q) use ($annee) {
                            $q->where('ANNEE_DECES', $annee);
                        })
                        ->count();

                    $naissances = Naissance::where('region_id', $region->id)
                        ->when($annee, function($q) use ($annee) {
                            $q->where('ANNEE_NAISSANCE', $annee);
                        })
                        ->count();

                    $districtsCount = $region->districts()->count();
                    $communesCount = $region->districts()->withCount('communes')->get()->sum('communes_count');

                    return [
                        'id' => $region->id,
                        'libelle' => $region->libelle,
                        'code' => $region->code,
                        'deces' => $deces,
                        'naissances' => $naissances,
                        'solde_naturel' => $naissances - $deces,
                        'taux_accroissement' => $deces > 0 ? round(($naissances / $deces) * 100, 2) : 0,
                        'structure_territoriale' => [
                            'districts' => $districtsCount,
                            'communes' => $communesCount,
                            'densite_administrative' => $communesCount > 0 ? round($districtsCount / $communesCount, 2) : 0,
                        ],
                        'densite_evenements' => $deces + $naissances,
                        'intensite_demographique' => $communesCount > 0 ? round(($deces + $naissances) / $communesCount, 2) : 0,
                    ];
                });

            // 📈 Classements
            $classementDeces = $regions->sortByDesc('deces')->values();
            $classementNaissances = $regions->sortByDesc('naissances')->values();
            $classementAccroissement = $regions->sortByDesc('taux_accroissement')->values();

            return response()->json([
                'success' => true,
                'message' => 'Comparaison des régions effectuée avec succès',
                'data' => [
                    'regions' => $regions,
                    'classements' => [
                        'par_deces' => $classementDeces,
                        'par_naissances' => $classementNaissances,
                        'par_accroissement' => $classementAccroissement,
                    ],
                    'synthese' => [
                        'region_plus_peuplee' => $classementDeces->first(),
                        'region_moins_peuplee' => $classementDeces->last(),
                        'region_plus_dynamique' => $classementAccroissement->first(),
                        'moyenne_deces' => round($regions->avg('deces'), 2),
                        'moyenne_naissances' => round($regions->avg('naissances'), 2),
                    ],
                    'filtres' => [
                        'region_ids' => $regionIds,
                        'annee' => $annee
                    ]
                ]
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
     * Calculer la tendance évolutive
     */
    private function calculerTendance($data)
    {
        if ($data->count() < 2) {
            return 0;
        }

        $recent = $data->take(2);
        $variation = (($recent[0]->total - $recent[1]->total) / $recent[1]->total) * 100;
        
        return round($variation, 2);
    }
}