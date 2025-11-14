<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deces;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class DecesController extends Controller
{
    /**
     * Liste tous les décès avec pagination et filtres avancés
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Deces::avecRelations();

            // ========== FILTRES AVANCÉS ==========
            
            // 🔍 Recherche globale
            if ($request->filled('search')) {
                $query->where(function($q) use ($request) {
                    $q->where('N_ACTE', 'LIKE', "%{$request->search}%")
                      ->orWhere('LIBCOM', 'LIKE', "%{$request->search}%")
                      ->orWhere('LIBDIST', 'LIKE', "%{$request->search}%")
                      ->orWhere('LIBREG', 'LIKE', "%{$request->search}%")
                      ->orWhere('LIBFKT', 'LIKE', "%{$request->search}%")
                      ->orWhere('LIB_CAUSE_DECES', 'LIKE', "%{$request->search}%")
                      ->orWhere('COM_DECE_L', 'LIKE', "%{$request->search}%")
                      ->orWhere('DIST_DECE_L', 'LIKE', "%{$request->search}%");
                });
            }

            // 📅 Filtres temporels
            if ($request->filled('annee')) {
                $query->where('ANNEE_DECES', $request->annee);
            }
            if ($request->filled('mois')) {
                $query->where('MOIS_DECES', $request->mois);
            }
            if ($request->filled('annee_debut') && $request->filled('annee_fin')) {
                $query->whereBetween('ANNEE_DECES', [$request->annee_debut, $request->annee_fin]);
            }

            // 🗺️ Filtres géographiques
            if ($request->filled('region_id')) {
                $query->where('region_id', $request->region_id);
            }
            if ($request->filled('district_id')) {
                $query->where('district_id', $request->district_id);
            }
            if ($request->filled('commune_id')) {
                $query->where('commune_id', $request->commune_id);
            }
            if ($request->filled('fokontany_id')) {
                $query->where('fokontany_id', $request->fokontany_id);
            }

            // 👥 Filtres démographiques
            if ($request->filled('sexe')) {
                $query->where('SEXE_DEFUNT', $request->sexe);
            }
            if ($request->filled('sanitaire')) {
                $query->where('SANITAIRE', $request->sanitaire);
            }
            if ($request->filled('milieu')) {
                $query->where('MILIEU', $request->milieu);
            }

            // 🏥 Filtres médicaux
            if ($request->filled('cause_deces_id')) {
                $query->where('cause_deces_id', $request->cause_deces_id);
            }
            if ($request->filled('lib_cause_deces')) {
                $query->where('LIB_CAUSE_DECES', 'LIKE', "%{$request->lib_cause_deces}%");
            }

            // 👨‍💼 Filtres professionnels
            if ($request->filled('profession_defunt_id')) {
                $query->where('profession_defunt_id', $request->profession_defunt_id);
            }
            if ($request->filled('profession_declarant_id')) {
                $query->where('profession_declarant_id', $request->profession_declarant_id);
            }

            // 🌍 Filtres nationalité
            if ($request->filled('nationalite_id')) {
                $query->where('nationalite_id', $request->nationalite_id);
            }

            // 🎂 Filtres âge
            if ($request->filled('age_min') && $request->filled('age_max')) {
                $query->whereNotNull('ANNEE_DECES')
                      ->whereNotNull('ANNEE_NAISSANCE_DEFUNT')
                      ->whereRaw('(ANNEE_DECES - ANNEE_NAISSANCE_DEFUNT) BETWEEN ? AND ?', 
                                [$request->age_min, $request->age_max]);
            }

            // ========== TRI ==========
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            $allowedSortColumns = [
                'created_at', 'updated_at', 'ANNEE_DECES', 'MOIS_DECES', 
                'JOUR_DECES', 'N_ACTE', 'ANNEE_NAISSANCE_DEFUNT'
            ];
            
            if (in_array($sortBy, $allowedSortColumns)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // ========== PAGINATION ==========
            $perPage = min($request->get('per_page', 20), 100);
            $deces = $query->paginate($perPage);

            // 📊 Statistiques de la requête
            $stats = [
                'total' => $deces->total(),
                'par_page' => $deces->perPage(),
                'page_courante' => $deces->currentPage(),
                'derniere_page' => $deces->lastPage(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Liste des décès récupérée avec succès',
                'data' => $deces->items(),
                'pagination' => $stats,
                'filtres_appliques' => $request->except(['page', 'per_page', 'sort_by', 'sort_order'])
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur récupération décès', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des décès',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * Créer un nouveau décès avec validation complète
     */
    public function store(Request $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $validator = Validator::make($request->all(), [
                // ========== RELATIONS ==========
                'region_id' => 'nullable|exists:regions,id',
                'district_id' => 'nullable|exists:districts,id',
                'commune_id' => 'nullable|exists:communes,id',
                'fokontany_id' => 'nullable|exists:fokontany,id',
                'cause_deces_id' => 'nullable|exists:causes_deces,id',
                'profession_defunt_id' => 'nullable|exists:professions,id',
                'profession_declarant_id' => 'nullable|exists:professions,id',
                'nationalite_id' => 'nullable|exists:nationalites,id',

                // ========== DATES ET TEMPS DÉCÈS ==========
                'ANNEE_DECES' => 'required|integer|min:1900|max:' . date('Y'),
                'MOIS_DECES' => 'required|integer|between:1,12',
                'JOUR_DECES' => 'required|integer|between:1,31',
                'HEUR_DECES' => 'nullable|integer|between:0,23',
                'MIN_DECES' => 'nullable|integer|between:0,59',
                'MOMENT_DECES' => 'nullable|string|max:50',
                
                // ========== INFORMATIONS DÉCLARATION ==========
                'ANNEE_DECL' => 'nullable|integer|min:1900|max:' . date('Y'),
                'MOIS_DECL' => 'nullable|integer|between:1,12',
                'JOUR_DECL' => 'nullable|integer|between:1,31',
                'ANNEE_CLASS' => 'nullable|integer|min:1900|max:' . date('Y'),
                'MOIS_CLASS' => 'nullable|integer|between:1,12',

                // ========== INFORMATIONS DÉFUNT ==========
                'SEXE_DEFUNT' => 'required|integer|in:1,2',
                'N_ACTE' => 'nullable|string|unique:deces_2020_24,N_ACTE',
                'ANNEE_NAISSANCE_DEFUNT' => 'nullable|integer|min:1900|max:' . date('Y'),
                'MOIS_NAISSANCE_DEFUNT' => 'nullable|integer|between:1,12',
                'JOUR_NAISSANCE_DEFUNT' => 'nullable|integer|between:1,31',
                'NATIONALITE_DEFUNT' => 'nullable|string|max:100',
                'SITUATION_MATRIMONIAL_DEFUNT' => 'nullable|integer|in:1,2,3,4',
                'PROFESSION_DEFUNT' => 'nullable|string|max:100',
                'PROFESSION_DEFUNT_L' => 'nullable|string|max:255',

                // ========== CAUSE DÉCÈS ==========
                'CAUSE_DECES' => 'nullable|string|max:100',
                'LIB_CAUSE_DECES' => 'nullable|string|max:255',

                // ========== INFORMATIONS DÉCLARANT ==========
                'LIEN_PAR_DECLARANT_DEFUNT' => 'nullable|string|max:100',
                'PROFESSION_DECLARANT' => 'nullable|string|max:100',
                'PROFESSION_DECLARANT_L' => 'nullable|string|max:255',

                // ========== LOCALISATIONS ==========
                'COMMUNE' => 'nullable|string|max:255',
                'LIBCOM' => 'nullable|string|max:255',
                'DISTRICT' => 'nullable|string|max:255',
                'LIBDIST' => 'nullable|string|max:255',
                'REGION' => 'nullable|string|max:255',
                'LIBREG' => 'nullable|string|max:255',
                'FOKONTANY' => 'nullable|string|max:255',
                'LIBFKT' => 'nullable|string|max:255',
                'MILIEU' => 'nullable|integer|in:1,2',
                'LIBMIL' => 'nullable|string|max:50',
                'SANITAIRE' => 'nullable|integer|in:1,2',

                // ========== LOCALISATIONS DÉTAILLÉES ==========
                'COM_DECE' => 'nullable|string|max:255',
                'COM_DECE_L' => 'nullable|string|max:255',
                'COM_ACTUELLE_DECLARANT' => 'nullable|string|max:255',
                'COM_ACTUELLE_DECLARANT_L' => 'nullable|string|max:255',
                'COM_ACTUELLE_DOMICILE' => 'nullable|string|max:255',
                'COM_ACTUELLE_DOMICILE_L' => 'nullable|string|max:255',
                'COMMUNE_NAISSANCE_DEFUNT' => 'nullable|string|max:255',
                'COMMUNE_NAISSANCE_DEFUNT_L' => 'nullable|string|max:255',

                'DIST_DECE' => 'nullable|string|max:255',
                'DIST_DECE_L' => 'nullable|string|max:255',
                'DIST_ACTUELLE_DECLARANT' => 'nullable|string|max:255',
                'DIST_ACTUELLE_DECLARANT_L' => 'nullable|string|max:255',
                'DIST_ACTUEL_DEFUNU' => 'nullable|string|max:255',
                'DIST_ACTUEL_DEFUNU_L' => 'nullable|string|max:255',
                'DISTRICT_NAISSANCE_DEFUNT' => 'nullable|string|max:255',
                'DISTRICT_NAISSANCE_DEFUNT_L' => 'nullable|string|max:255',

                'FOKONTANY_ACTUELLE_DOMICILE' => 'nullable|string|max:255',
                'FOKONTANY_ACTUELLE_DOMICILE_L' => 'nullable|string|max:255',
                'FOKONTANY_NAISSANCE_DEFUNT' => 'nullable|string|max:255',
                'FOKONTANY_NAISSANCE_DEFUNT_L' => 'nullable|string|max:255',

                // ========== AUTRES CHAMPS ==========
                'DFIN' => 'nullable|string|max:50',
                'IDFKT' => 'nullable|string|max:50',
            ], [
                'ANNEE_DECES.required' => 'L\'année de décès est obligatoire',
                'MOIS_DECES.required' => 'Le mois de décès est obligatoire',
                'JOUR_DECES.required' => 'Le jour de décès est obligatoire',
                'SEXE_DEFUNT.required' => 'Le sexe du défunt est obligatoire',
                'N_ACTE.unique' => 'Ce numéro d\'acte existe déjà',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 📅 Validation cohérence dates décès
            if ($request->ANNEE_DECES && $request->MOIS_DECES && $request->JOUR_DECES) {
                if (!checkdate($request->MOIS_DECES, $request->JOUR_DECES, $request->ANNEE_DECES)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreur de validation',
                        'errors' => ['date_deces' => ['La date de décès est invalide']]
                    ], 422);
                }
            }

            // 📅 Validation cohérence dates naissance défunt
            if ($request->ANNEE_NAISSANCE_DEFUNT && $request->MOIS_NAISSANCE_DEFUNT && $request->JOUR_NAISSANCE_DEFUNT) {
                if (!checkdate($request->MOIS_NAISSANCE_DEFUNT, $request->JOUR_NAISSANCE_DEFUNT, $request->ANNEE_NAISSANCE_DEFUNT)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreur de validation',
                        'errors' => ['date_naissance_defunt' => ['La date de naissance du défunt est invalide']]
                    ], 422);
                }
            }

            $deces = Deces::create($request->all());
            
            // 🔄 Charger toutes les relations
            $deces->load([
                'region', 'district', 'commune', 'fokontany',
                'causeDeces', 'professionDefunt', 'professionDeclarant', 'nationalite'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Décès enregistré avec succès',
                'data' => $deces
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Erreur enregistrement décès', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement du décès',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * Afficher un décès spécifique
     */
    public function show(int $id): JsonResponse
    {
        try {
            $deces = Deces::avecRelations()->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Décès récupéré avec succès',
                'data' => $deces
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Décès non trouvé', [
                'error' => $e->getMessage(),
                'deces_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Décès non trouvé',
                'error' => 'Le décès demandé n\'existe pas'
            ], 404);
        }
    }

    /**
     * Mettre à jour un décès
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $deces = Deces::findOrFail($id);

            $validator = Validator::make($request->all(), [
                // ========== RELATIONS ==========
                'region_id' => 'nullable|exists:regions,id',
                'district_id' => 'nullable|exists:districts,id',
                'commune_id' => 'nullable|exists:communes,id',
                'fokontany_id' => 'nullable|exists:fokontany,id',
                'cause_deces_id' => 'nullable|exists:causes_deces,id',
                'profession_defunt_id' => 'nullable|exists:professions,id',
                'profession_declarant_id' => 'nullable|exists:professions,id',
                'nationalite_id' => 'nullable|exists:nationalites,id',

                // ========== DATES ET TEMPS ==========
                'ANNEE_DECES' => 'sometimes|required|integer|min:1900|max:' . date('Y'),
                'MOIS_DECES' => 'sometimes|required|integer|between:1,12',
                'JOUR_DECES' => 'sometimes|required|integer|between:1,31',
                'HEUR_DECES' => 'nullable|integer|between:0,23',
                'MIN_DECES' => 'nullable|integer|between:0,59',

                // ========== INFORMATIONS DÉFUNT ==========
                'SEXE_DEFUNT' => 'sometimes|required|integer|in:1,2',
                'N_ACTE' => 'nullable|string|unique:deces_2020_24,N_ACTE,' . $id,
                'ANNEE_NAISSANCE_DEFUNT' => 'nullable|integer|min:1900|max:' . date('Y'),
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 📅 Validation cohérence dates si fournies
            if ($request->has(['ANNEE_DECES', 'MOIS_DECES', 'JOUR_DECES'])) {
                if (!checkdate($request->MOIS_DECES, $request->JOUR_DECES, $request->ANNEE_DECES)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreur de validation',
                        'errors' => ['date_deces' => ['La date de décès est invalide']]
                    ], 422);
                }
            }

            $deces->update($request->all());
            $deces->load([
                'region', 'district', 'commune', 'fokontany',
                'causeDeces', 'professionDefunt', 'professionDeclarant', 'nationalite'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Décès mis à jour avec succès',
                'data' => $deces
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Erreur mise à jour décès', [
                'error' => $e->getMessage(),
                'deces_id' => $id,
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du décès',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * Supprimer un décès
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $deces = Deces::findOrFail($id);
            $deces->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Décès supprimé avec succès',
                'id' => $id
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Erreur suppression décès', [
                'error' => $e->getMessage(),
                'deces_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du décès',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * Statistiques avancées des décès
     */
    public function statistiques(Request $request): JsonResponse
    {
        try {
            $query = Deces::query();

            // 🎛️ Filtres pour les statistiques
            if ($request->filled('annee')) {
                $query->where('ANNEE_DECES', $request->annee);
            }
            if ($request->filled('region_id')) {
                $query->where('region_id', $request->region_id);
            }
            if ($request->filled('district_id')) {
                $query->where('district_id', $request->district_id);
            }
            if ($request->filled('sexe')) {
                $query->where('SEXE_DEFUNT', $request->sexe);
            }

            $stats = [
                // 📊 Totaux
                'total_deces' => (clone $query)->count(),
                'deces_hopital' => (clone $query)->where('SANITAIRE', 1)->count(),
                'deces_domicile' => (clone $query)->where('SANITAIRE', 2)->count(),
                
                // ⚥ Répartition par sexe
                'par_sexe' => (clone $query)->selectRaw('SEXE_DEFUNT, COUNT(*) as total')
                    ->whereNotNull('SEXE_DEFUNT')
                    ->groupBy('SEXE_DEFUNT')
                    ->get(),
                
                // 📅 Répartition temporelle
                'par_mois' => (clone $query)->selectRaw('MOIS_DECES, COUNT(*) as total')
                    ->whereNotNull('MOIS_DECES')
                    ->groupBy('MOIS_DECES')
                    ->orderBy('MOIS_DECES')
                    ->get(),
                'par_annee' => (clone $query)->selectRaw('ANNEE_DECES, COUNT(*) as total')
                    ->whereNotNull('ANNEE_DECES')
                    ->groupBy('ANNEE_DECES')
                    ->orderBy('ANNEE_DECES', 'desc')
                    ->get(),
                
                // 🏥 Causes de décès
                'causes_frequentes' => (clone $query)->selectRaw('LIB_CAUSE_DECES, COUNT(*) as total')
                    ->whereNotNull('LIB_CAUSE_DECES')
                    ->groupBy('LIB_CAUSE_DECES')
                    ->orderBy('total', 'desc')
                    ->limit(10)
                    ->get(),
                
                // 🏙️ Milieu
                'par_milieu' => (clone $query)->selectRaw('MILIEU, COUNT(*) as total')
                    ->whereNotNull('MILIEU')
                    ->groupBy('MILIEU')
                    ->get(),
                
                // 🎂 Pyramide des âges
                'pyramide_ages' => (clone $query)->selectRaw('
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
                    ->groupBy('tranche_age', 'SEXE_DEFUNT')
                    ->orderByRaw('MIN(ANNEE_DECES - ANNEE_NAISSANCE_DEFUNT)')
                    ->get(),
                
                // 🗺️ Répartition géographique
                'par_region' => (clone $query)->selectRaw('REGION, COUNT(*) as total')
                    ->whereNotNull('REGION')
                    ->groupBy('REGION')
                    ->orderBy('total', 'desc')
                    ->get(),
                'par_district' => (clone $query)->selectRaw('DISTRICT, COUNT(*) as total')
                    ->whereNotNull('DISTRICT')
                    ->groupBy('DISTRICT')
                    ->orderBy('total', 'desc')
                    ->limit(15)
                    ->get(),
                
                // 💼 Professions
                'professions_defunts' => (clone $query)->selectRaw('PROFESSION_DEFUNT_L, COUNT(*) as total')
                    ->whereNotNull('PROFESSION_DEFUNT_L')
                    ->groupBy('PROFESSION_DEFUNT_L')
                    ->orderBy('total', 'desc')
                    ->limit(10)
                    ->get(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Statistiques des décès récupérées avec succès',
                'data' => $stats,
                'filtres' => $request->all()
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur statistiques décès', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul des statistiques',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }
}