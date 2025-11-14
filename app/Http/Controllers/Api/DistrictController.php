<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\Deces;
use App\Models\Naissance;
use App\Models\Commune;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DistrictController extends Controller
{
    /**
     * Liste tous les districts avec filtres avancés
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = District::with(['region', 'communes']);

            // ========== FILTRES AVANCÉS ==========
            
            // 🗺️ Filtre par région
            if ($request->filled('region_id')) {
                $query->where('region_id', $request->region_id);
            }

            // 🔍 Recherche par nom
            if ($request->filled('search')) {
                $query->where(function($q) use ($request) {
                    $q->where('libelle', 'LIKE', "%{$request->search}%")
                      ->orWhere('code', 'LIKE', "%{$request->search}%")
                      ->orWhereHas('region', function($q) use ($request) {
                          $q->where('libelle', 'LIKE', "%{$request->search}%");
                      });
                });
            }

            // 📊 Filtre avec statistiques
            if ($request->filled('avec_stats')) {
                $query->withCount(['deces', 'naissances']);
            }

            // ========== TRI ==========
            $sortBy = $request->get('sort_by', 'libelle');
            $sortOrder = $request->get('sort_order', 'asc');
            
            $allowedSortColumns = ['libelle', 'code', 'created_at', 'deces_count', 'naissances_count'];
            if (in_array($sortBy, $allowedSortColumns)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('libelle', 'asc');
            }

            // ========== PAGINATION ==========
            if ($request->filled('paginate')) {
                $perPage = min($request->get('per_page', 20), 100);
                $districts = $query->paginate($perPage);

                $pagination = [
                    'total' => $districts->total(),
                    'par_page' => $districts->perPage(),
                    'page_courante' => $districts->currentPage(),
                    'derniere_page' => $districts->lastPage(),
                ];
            } else {
                $districts = $query->get();
                $pagination = null;
            }

            return response()->json([
                'success' => true,
                'message' => 'Liste des districts récupérée avec succès',
                'data' => $districts,
                'pagination' => $pagination,
                'filtres_appliques' => $request->except(['page', 'per_page', 'sort_by', 'sort_order'])
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur récupération districts', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des districts',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * Afficher un district spécifique avec données complètes
     */
    public function show(int $id): JsonResponse
    {
        try {
            $district = District::with([
                'region', 
                'communes' => function($query) {
                    $query->orderBy('libelle')->withCount(['deces', 'naissances']);
                }
            ])->findOrFail($id);

            // 📊 Statistiques rapides
            $statsRapides = [
                'nombre_communes' => $district->communes->count(),
                'total_deces' => $district->deces()->count(),
                'total_naissances' => $district->naissances()->count(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'District récupéré avec succès',
                'data' => [
                    'district' => $district,
                    'statistiques_rapides' => $statsRapides
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('District non trouvé', [
                'error' => $e->getMessage(),
                'district_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'District non trouvé',
                'error' => 'Le district demandé n\'existe pas'
            ], 404);
        }
    }

    /**
     * Obtenir les communes d'un district avec statistiques
     */
    public function communes(int $id, Request $request): JsonResponse
    {
        try {
            $district = District::findOrFail($id);
            
            $query = $district->communes()->withCount(['deces', 'naissances']);

            // 🔍 Recherche dans les communes
            if ($request->filled('search')) {
                $query->where('libelle', 'LIKE', "%{$request->search}%")
                      ->orWhere('code', 'LIKE', "%{$request->search}%");
            }

            // 📊 Tri par statistiques
            $sortBy = $request->get('sort_by', 'libelle');
            $sortOrder = $request->get('sort_order', 'asc');
            
            if (in_array($sortBy, ['deces_count', 'naissances_count'])) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('libelle', $sortOrder);
            }

            $communes = $query->get();

            return response()->json([
                'success' => true,
                'message' => 'Communes récupérées avec succès',
                'data' => [
                    'district' => $district->only(['id', 'libelle', 'code']),
                    'communes' => $communes,
                    'total_communes' => $communes->count()
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur récupération communes', [
                'error' => $e->getMessage(),
                'district_id' => $id,
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des communes',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques détaillées d'un district
     */
    public function statistiques(int $id, Request $request): JsonResponse
    {
        try {
            $district = District::with(['region'])->findOrFail($id);
            $annee = $request->get('annee');

            // ========== STATISTIQUES DÉCÈS ==========
            $queryDeces = Deces::where('district_id', $id);
            $queryNaissances = Naissance::where('district_id', $id);

            if ($annee) {
                $queryDeces->where('ANNEE_DECES', $annee);
                $queryNaissances->where('ANNEE_NAISSANCE', $annee);
            }

            // 📊 Décès
            $totalDeces = $queryDeces->count();
            $decesParSexe = $queryDeces->selectRaw('SEXE_DEFUNT, COUNT(*) as total')
                ->whereNotNull('SEXE_DEFUNT')
                ->groupBy('SEXE_DEFUNT')
                ->get();

            $decesParAnnee = Deces::where('district_id', $id)
                ->selectRaw('ANNEE_DECES as annee, COUNT(*) as total')
                ->whereNotNull('ANNEE_DECES')
                ->groupBy('ANNEE_DECES')
                ->orderBy('ANNEE_DECES', 'desc')
                ->limit(5)
                ->get();

            $causesDeces = Deces::where('district_id', $id)
                ->selectRaw('LIB_CAUSE_DECES, COUNT(*) as total')
                ->whereNotNull('LIB_CAUSE_DECES')
                ->when($annee, function($q) use ($annee) {
                    $q->where('ANNEE_DECES', $annee);
                })
                ->groupBy('LIB_CAUSE_DECES')
                ->orderBy('total', 'desc')
                ->limit(8)
                ->get();

            // 👶 Naissances
            $totalNaissances = $queryNaissances->count();
            $naissancesParSexe = $queryNaissances->selectRaw('SEXE_ENFANT, COUNT(*) as total')
                ->whereNotNull('SEXE_ENFANT')
                ->groupBy('SEXE_ENFANT')
                ->get();

            $naissancesParAnnee = Naissance::where('district_id', $id)
                ->selectRaw('ANNEE_NAISSANCE as annee, COUNT(*) as total')
                ->whereNotNull('ANNEE_NAISSANCE')
                ->groupBy('ANNEE_NAISSANCE')
                ->orderBy('ANNEE_NAISSANCE', 'desc')
                ->limit(5)
                ->get();

            $assistanceMedicale = Naissance::where('district_id', $id)
                ->selectRaw('NAISS_ASSIS_PERS_SANTE, COUNT(*) as total')
                ->whereNotNull('NAISS_ASSIS_PERS_SANTE')
                ->when($annee, function($q) use ($annee) {
                    $q->where('ANNEE_NAISSANCE', $annee);
                })
                ->groupBy('NAISS_ASSIS_PERS_SANTE')
                ->get();

            // 🏙️ Milieu urbain/rural
            $naissancesParMilieu = Naissance::where('district_id', $id)
                ->selectRaw('MILIEU, COUNT(*) as total')
                ->whereNotNull('MILIEU')
                ->when($annee, function($q) use ($annee) {
                    $q->where('ANNEE_NAISSANCE', $annee);
                })
                ->groupBy('MILIEU')
                ->get();

            $decesParMilieu = Deces::where('district_id', $id)
                ->selectRaw('MILIEU, COUNT(*) as total')
                ->whereNotNull('MILIEU')
                ->when($annee, function($q) use ($annee) {
                    $q->where('ANNEE_DECES', $annee);
                })
                ->groupBy('MILIEU')
                ->get();

            // 🎂 Pyramide des âges locale
            $pyramideAges = Deces::where('district_id', $id)
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

            // 🗺️ Répartition par commune
            $decesParCommune = Deces::where('district_id', $id)
                ->selectRaw('commune_id, COUNT(*) as total')
                ->with(['commune'])
                ->whereNotNull('commune_id')
                ->when($annee, function($q) use ($annee) {
                    $q->where('ANNEE_DECES', $annee);
                })
                ->groupBy('commune_id')
                ->orderBy('total', 'desc')
                ->limit(10)
                ->get();

            $naissancesParCommune = Naissance::where('district_id', $id)
                ->selectRaw('commune_id, COUNT(*) as total')
                ->with(['commune'])
                ->whereNotNull('commune_id')
                ->when($annee, function($q) use ($annee) {
                    $q->where('ANNEE_NAISSANCE', $annee);
                })
                ->groupBy('commune_id')
                ->orderBy('total', 'desc')
                ->limit(10)
                ->get();

            // 📈 Indicateurs calculés
            $tauxAccroissement = $totalDeces > 0 ? round(($totalNaissances / $totalDeces) * 100, 2) : 0;
            $soldeNaturel = $totalNaissances - $totalDeces;
            $tauxAssistance = $assistanceMedicale->sum('total') > 0 ? 
                round(($assistanceMedicale->where('NAISS_ASSIS_PERS_SANTE', 1)->first()->total ?? 0) / $assistanceMedicale->sum('total') * 100, 2) : 0;

            return response()->json([
                'success' => true,
                'message' => 'Statistiques détaillées du district récupérées avec succès',
                'data' => [
                    'district' => $district,
                    'filtres' => ['annee' => $annee],
                    'indicateurs_cles' => [
                        'total_deces' => $totalDeces,
                        'total_naissances' => $totalNaissances,
                        'solde_naturel' => $soldeNaturel,
                        'taux_accroissement' => $tauxAccroissement,
                        'taux_assistance_medicale' => $tauxAssistance,
                        'ratio_naissance_deces' => $totalDeces > 0 ? round($totalNaissances / $totalDeces, 2) : 0,
                    ],
                    'deces' => [
                        'total' => $totalDeces,
                        'par_sexe' => $decesParSexe,
                        'par_annee' => $decesParAnnee,
                        'causes_frequentes' => $causesDeces,
                        'par_milieu' => $decesParMilieu,
                    ],
                    'naissances' => [
                        'total' => $totalNaissances,
                        'par_sexe' => $naissancesParSexe,
                        'par_annee' => $naissancesParAnnee,
                        'assistance_medicale' => $assistanceMedicale,
                        'par_milieu' => $naissancesParMilieu,
                    ],
                    'analyse_demographique' => [
                        'pyramide_ages' => $pyramideAges,
                    ],
                    'repartition_geographique' => [
                        'deces_par_commune' => $decesParCommune,
                        'naissances_par_commune' => $naissancesParCommune,
                    ],
                    'communes_actives' => [
                        'nombre_total_communes' => $district->communes()->count(),
                        'communes_avec_donnees' => $decesParCommune->count() + $naissancesParCommune->count(),
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur statistiques district', [
                'error' => $e->getMessage(),
                'district_id' => $id,
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
     * Comparaison de plusieurs districts
     */
    public function comparaison(Request $request): JsonResponse
    {
        try {
            $districtIds = $request->get('district_ids', []);
            $annee = $request->get('annee', date('Y'));

            if (empty($districtIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun district spécifié pour la comparaison'
                ], 400);
            }

            $districts = District::with(['region'])
                ->whereIn('id', $districtIds)
                ->get()
                ->map(function($district) use ($annee) {
                    $deces = Deces::where('district_id', $district->id)
                        ->when($annee, function($q) use ($annee) {
                            $q->where('ANNEE_DECES', $annee);
                        })
                        ->count();

                    $naissances = Naissance::where('district_id', $district->id)
                        ->when($annee, function($q) use ($annee) {
                            $q->where('ANNEE_NAISSANCE', $annee);
                        })
                        ->count();

                    return [
                        'id' => $district->id,
                        'libelle' => $district->libelle,
                        'code' => $district->code,
                        'region' => $district->region->libelle,
                        'deces' => $deces,
                        'naissances' => $naissances,
                        'solde_naturel' => $naissances - $deces,
                        'taux_accroissement' => $deces > 0 ? round(($naissances / $deces) * 100, 2) : 0,
                        'densite' => $deces + $naissances, // indicateur de densité d'événements
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Comparaison des districts effectuée avec succès',
                'data' => [
                    'districts' => $districts,
                    'filtres' => [
                        'district_ids' => $districtIds,
                        'annee' => $annee
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur comparaison districts', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la comparaison des districts',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }
}