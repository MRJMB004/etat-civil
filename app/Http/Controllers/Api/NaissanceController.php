<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Naissance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class NaissanceController extends Controller
{
    /**
     * Liste toutes les naissances avec pagination et filtres avancés
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Naissance::withRelations();

            // ========== FILTRES AVANCÉS ==========
            
            // 🔍 Recherche globale
            if ($request->filled('search')) {
                $query->where(function($q) use ($request) {
                    $q->where('NOM_ENFANT', 'LIKE', "%{$request->search}%")
                      ->orWhere('PRENOM_ENFANT', 'LIKE', "%{$request->search}%")
                      ->orWhere('NOM_PERE', 'LIKE', "%{$request->search}%")
                      ->orWhere('NOM_MERE', 'LIKE', "%{$request->search}%")
                      ->orWhere('N_ACTE', 'LIKE', "%{$request->search}%")
                      ->orWhere('LIBCOM', 'LIKE', "%{$request->search}%")
                      ->orWhere('LIBDIST', 'LIKE', "%{$request->search}%")
                      ->orWhere('LIBREG', 'LIKE', "%{$request->search}%")
                      ->orWhere('LIBFKT', 'LIKE', "%{$request->search}%")
                      ->orWhere('NATIONALITE_MERE', 'LIKE', "%{$request->search}%")
                      ->orWhere('NATIONALITE_PERE', 'LIKE', "%{$request->search}%")
                      ->orWhere('PROF_MERE_L', 'LIKE', "%{$request->search}%")
                      ->orWhere('PROF_PERE_L', 'LIKE', "%{$request->search}%");
                });
            }

            // 📅 Filtres temporels
            if ($request->filled('annee')) {
                $query->where('ANNEE_NAISSANCE', $request->annee);
            }
            if ($request->filled('mois')) {
                $query->where('MOIS_NAISSANCE', $request->mois);
            }
            if ($request->filled('annee_debut') && $request->filled('annee_fin')) {
                $query->whereBetween('ANNEE_NAISSANCE', [$request->annee_debut, $request->annee_fin]);
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
                $query->where('SEXE_ENFANT', $request->sexe);
            }
            if ($request->filled('naiss_viv_mort_ne')) {
                $query->where('NAISS_VIV_MORT_NE', $request->naiss_viv_mort_ne);
            }
            if ($request->filled('naiss_assist_pers_sante')) {
                $query->where('NAISS_ASSIS_PERS_SANTE', $request->naiss_assist_pers_sante);
            }
            if ($request->filled('milieu')) {
                $query->where('MILIEU', $request->milieu);
            }

            // 👨‍👩‍👧‍👦 Filtres parents
            if ($request->filled('age_mere_min') && $request->filled('age_mere_max')) {
                $query->whereBetween('AGE_MERE', [$request->age_mere_min, $request->age_mere_max]);
            }
            if ($request->filled('age_pere_min') && $request->filled('age_pere_max')) {
                $query->whereBetween('AGE_PERE', [$request->age_pere_min, $request->age_pere_max]);
            }
            if ($request->filled('annee_naissance_mere')) {
                $query->where('ANNEE_NAISS_MERE', $request->annee_naissance_mere);
            }
            if ($request->filled('annee_naissance_pere')) {
                $query->where('ANNEE_NAISS_PERE', $request->annee_naissance_pere);
            }
            if ($request->filled('nationalite_mere')) {
                $query->where('NATIONALITE_MERE', 'LIKE', "%{$request->nationalite_mere}%");
            }
            if ($request->filled('nationalite_pere')) {
                $query->where('NATIONALITE_PERE', 'LIKE', "%{$request->nationalite_pere}%");
            }
            if ($request->filled('profession_mere')) {
                $query->where('PROF_MERE_L', 'LIKE', "%{$request->profession_mere}%");
            }
            if ($request->filled('profession_pere')) {
                $query->where('PROF_PERE_L', 'LIKE', "%{$request->profession_pere}%");
            }

            // ========== TRI ==========
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            $allowedSortColumns = [
                'created_at', 'updated_at', 'ANNEE_NAISSANCE', 'MOIS_NAISSANCE', 
                'JOUR_NAISSANCE', 'N_ACTE', 'AGE_MERE', 'AGE_PERE',
                'ANNEE_NAISS_MERE', 'ANNEE_NAISS_PERE'
            ];
            
            if (in_array($sortBy, $allowedSortColumns)) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // ========== PAGINATION ==========
            $perPage = min($request->get('per_page', 20), 100);
            $naissances = $query->paginate($perPage);

            // 📊 Statistiques de la requête
            $stats = [
                'total' => $naissances->total(),
                'par_page' => $naissances->perPage(),
                'page_courante' => $naissances->currentPage(),
                'derniere_page' => $naissances->lastPage(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Liste des naissances récupérée avec succès',
                'data' => $naissances->items(),
                'pagination' => $stats,
                'filtres_appliques' => $request->except(['page', 'per_page', 'sort_by', 'sort_order'])
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur récupération naissances', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des naissances',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * Créer une nouvelle naissance avec validation complète
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
                'profession_mere_id' => 'nullable|exists:professions,id',
                'profession_pere_id' => 'nullable|exists:professions,id',
                'nationalite_mere_id' => 'nullable|exists:nationalites,id',
                'nationalite_pere_id' => 'nullable|exists:nationalites,id',

                // ========== DATES ET TEMPS NAISSANCE ==========
                'ANNEE_NAISSANCE' => 'required|integer|min:1900|max:' . (date('Y') + 1),
                'MOIS_NAISSANCE' => 'required|integer|between:1,12',
                'JOUR_NAISSANCE' => 'required|integer|between:1,31',
                'HEUR_DE_NAISSANCE' => 'nullable|integer|between:0,23',
                'MIN_DE_NAISSANCE' => 'nullable|integer|between:0,59',
                'MOMENT_DE_NAISSANCE' => 'nullable|string|max:50',
                
                // ========== INFORMATIONS DÉCLARATION ==========
                'ANNEE_DECLARATION' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
                'MOIS_DECLARATION' => 'nullable|integer|between:1,12',
                'JOUR_DECLARATION' => 'nullable|integer|between:1,31',

                // ========== INFORMATIONS ENFANT ==========
                'SEXE_ENFANT' => 'required|integer|in:1,2',
                'N_ACTE' => 'nullable|string|unique:naissance_2020_24,N_ACTE',
                'NOM_ENFANT' => 'required|string|max:255',
                'PRENOM_ENFANT' => 'nullable|string|max:255',
                'LIB_NAISSANCE_ENFANT' => 'nullable|string|max:255',
                'NAISS_VIV_MORT_NE' => 'nullable|integer|in:1,2',
                'NAISS_ASSIS_PERS_SANTE' => 'nullable|integer|in:1,2',
                'NAISS_FORM_SANITAIRE' => 'nullable|integer|in:1,2',

                // ========== INFORMATIONS PARENTS ==========
                'AGE_MERE' => 'nullable|numeric|min:10|max:100',
                'AGE_PERE' => 'nullable|numeric|min:10|max:100',
                'EXISTENCE_PERE' => 'nullable|integer|in:1,2',
                'NOM_MERE' => 'nullable|string|max:255',
                'NOM_PERE' => 'nullable|string|max:255',

                // ========== DATES NAISSANCE PARENTS ==========
                'ANNEE_NAISS_MERE' => 'nullable|integer|min:1900|max:' . date('Y'),
                'MOIS_NAISS_MERE' => 'nullable|integer|between:1,12',
                'JOUR_NAISS_MERE' => 'nullable|integer|between:1,31',
                'ANNEE_NAISS_PERE' => 'nullable|integer|min:1900|max:' . date('Y'),
                'MOIS_NAISS_PERE' => 'nullable|integer|between:1,12',
                'JOUR_NAISS_PERE' => 'nullable|integer|between:1,31',

                // ========== INFORMATIONS REGISTRE ==========
                'ANNEE_REGISTRE' => 'nullable|integer|min:1900|max:' . date('Y'),
                'MOIS_REGISTRE' => 'nullable|integer|between:1,12',
                'JOUR_REGISTRE' => 'nullable|integer|between:1,31',
                'ANNEE_EXACTE_ENREGISTREMENT_ACTE' => 'nullable|integer|min:1900|max:' . date('Y'),
                'MOIS_EXACT_ENREGISTREMENT_ACT' => 'nullable|integer|between:1,12',

                // ========== LOCALISATIONS ==========
                'COMMUNE' => 'nullable|string|max:255',
                'DISTRICT' => 'nullable|string|max:255',
                'REGION' => 'nullable|string|max:255',
                'FOKONTANY' => 'nullable|string|max:255',
                'MILIEU' => 'nullable|integer|in:1,2',

                // ========== CODES GÉOGRAPHIQUES ==========
                'COD_COM_LIEU_ACTUEL_MERE' => 'nullable|string|max:20',
                'COD_COM_LIEU_ACTUEL_PERE' => 'nullable|string|max:20',
                'COD_COM_LIEU_NAISS_MERE' => 'nullable|string|max:20',
                'COD_COM_LIEU_NAISS_PERE' => 'nullable|string|max:20',
                'COD_COM_RESID_DECLARANT' => 'nullable|string|max:20',
                'COD_DIST_LIEU_ACTUEL_MERE' => 'nullable|string|max:20',
                'COD_DIST_LIEU_ACTUEL_PERE' => 'nullable|string|max:20',
                'COD_DIST_LIEU_NAISS_MERE' => 'nullable|string|max:20',
                'COD_DIST_LIEU_NAISS_PERE' => 'nullable|string|max:20',
                'COD_DIST_RESID_DECLARANT' => 'nullable|string|max:20',

                // ========== LIBELLÉS GÉOGRAPHIQUES ==========
                'LIB_COM_LIEU_ACTUEL_MERE' => 'nullable|string|max:255',
                'LIB_COM_LIEU_ACTUEL_PERE' => 'nullable|string|max:255',
                'LIB_COM_LIEU_NAISS_MERE' => 'nullable|string|max:255',
                'LIB_COM_LIEU_NAISS_PERE' => 'nullable|string|max:255',
                'LIB_COM_RESID_DECLARANT' => 'nullable|string|max:255',
                'LIB_DIST_LIEU_ACTUEL_MERE' => 'nullable|string|max:255',
                'LIB_DIST_LIEU_ACTUEL_PERE' => 'nullable|string|max:255',
                'LIB_DIST_LIEU_NAISS_MERE' => 'nullable|string|max:255',
                'LIB_DIST_LIEU_NAISS_PERE' => 'nullable|string|max:255',
                'LIB_DIST_RESID_DECLARANT' => 'nullable|string|max:255',

                // ========== NATIONALITÉS ET PROFESSIONS ==========
                'NATIONALITE_MERE' => 'nullable|string|max:100',
                'NATIONALITE_PERE' => 'nullable|string|max:100',
                'PROF_MERE' => 'nullable|string|max:100',
                'PROF_MERE_L' => 'nullable|string|max:255',
                'PROF_PERE' => 'nullable|string|max:100',
                'PROF_PERE_L' => 'nullable|string|max:255',

                // ========== AUTRES CHAMPS ==========
                'LIEN_PARENTE_DELC' => 'nullable|string|max:100',
                'TYPE_ENREG' => 'nullable|string|max:50',
                'SFIN' => 'nullable|string|max:50',
            ], [
                'ANNEE_NAISSANCE.required' => 'L\'année de naissance est obligatoire',
                'MOIS_NAISSANCE.required' => 'Le mois de naissance est obligatoire',
                'JOUR_NAISSANCE.required' => 'Le jour de naissance est obligatoire',
                'SEXE_ENFANT.required' => 'Le sexe de l\'enfant est obligatoire',
                'NOM_ENFANT.required' => 'Le nom de l\'enfant est obligatoire',
                'N_ACTE.unique' => 'Ce numéro d\'acte existe déjà',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 📅 Validation cohérence dates naissance
            if ($request->ANNEE_NAISSANCE && $request->MOIS_NAISSANCE && $request->JOUR_NAISSANCE) {
                if (!checkdate($request->MOIS_NAISSANCE, $request->JOUR_NAISSANCE, $request->ANNEE_NAISSANCE)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreur de validation',
                        'errors' => ['date_naissance' => ['La date de naissance est invalide']]
                    ], 422);
                }
            }

            // 📅 Validation cohérence dates mère
            if ($request->ANNEE_NAISS_MERE && $request->MOIS_NAISS_MERE && $request->JOUR_NAISS_MERE) {
                if (!checkdate($request->MOIS_NAISS_MERE, $request->JOUR_NAISS_MERE, $request->ANNEE_NAISS_MERE)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreur de validation',
                        'errors' => ['date_naissance_mere' => ['La date de naissance de la mère est invalide']]
                    ], 422);
                }
            }

            // 📅 Validation cohérence dates père
            if ($request->ANNEE_NAISS_PERE && $request->MOIS_NAISS_PERE && $request->JOUR_NAISS_PERE) {
                if (!checkdate($request->MOIS_NAISS_PERE, $request->JOUR_NAISS_PERE, $request->ANNEE_NAISS_PERE)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreur de validation',
                        'errors' => ['date_naissance_pere' => ['La date de naissance du père est invalide']]
                    ], 422);
                }
            }

            $naissance = Naissance::create($request->all());
            
            // 🔄 Charger toutes les relations
            $naissance->load([
                'region', 'district', 'commune', 'fokontany',
                'professionMere', 'professionPere', 
                'nationaliteMere', 'nationalitePere'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Naissance enregistrée avec succès',
                'data' => $naissance
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Erreur enregistrement naissance', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'enregistrement de la naissance',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * Afficher une naissance spécifique
     */
    public function show(int $id): JsonResponse
    {
        try {
            $naissance = Naissance::avecRelations()->findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Naissance récupérée avec succès',
                'data' => $naissance
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Naissance non trouvée', [
                'error' => $e->getMessage(),
                'naissance_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Naissance non trouvée',
                'error' => 'La naissance demandée n\'existe pas'
            ], 404);
        }
    }

    /**
     * Mettre à jour une naissance
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $naissance = Naissance::findOrFail($id);

            $validator = Validator::make($request->all(), [
                // ========== RELATIONS ==========
                'region_id' => 'nullable|exists:regions,id',
                'district_id' => 'nullable|exists:districts,id',
                'commune_id' => 'nullable|exists:communes,id',
                'fokontany_id' => 'nullable|exists:fokontany,id',
                'profession_mere_id' => 'nullable|exists:professions,id',
                'profession_pere_id' => 'nullable|exists:professions,id',
                'nationalite_mere_id' => 'nullable|exists:nationalites,id',
                'nationalite_pere_id' => 'nullable|exists:nationalites,id',

                // ========== DATES ET TEMPS ==========
                'ANNEE_NAISSANCE' => 'sometimes|required|integer|min:1900|max:' . (date('Y') + 1),
                'MOIS_NAISSANCE' => 'sometimes|required|integer|between:1,12',
                'JOUR_NAISSANCE' => 'sometimes|required|integer|between:1,31',
                'HEUR_DE_NAISSANCE' => 'nullable|integer|between:0,23',
                'MIN_DE_NAISSANCE' => 'nullable|integer|between:0,59',

                // ========== INFORMATIONS ENFANT ==========
                'SEXE_ENFANT' => 'sometimes|required|integer|in:1,2',
                'N_ACTE' => 'nullable|string|unique:naissance_2020_24,N_ACTE,' . $id,
                'NOM_ENFANT' => 'sometimes|required|string|max:255',
                'PRENOM_ENFANT' => 'nullable|string|max:255',

                // ========== INFORMATIONS PARENTS ==========
                'AGE_MERE' => 'nullable|numeric|min:10|max:100',
                'AGE_PERE' => 'nullable|numeric|min:10|max:100',
                'ANNEE_NAISS_MERE' => 'nullable|integer|min:1900|max:' . date('Y'),
                'ANNEE_NAISS_PERE' => 'nullable|integer|min:1900|max:' . date('Y'),
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // 📅 Validation cohérence dates si fournies
            if ($request->has(['ANNEE_NAISSANCE', 'MOIS_NAISSANCE', 'JOUR_NAISSANCE'])) {
                if (!checkdate($request->MOIS_NAISSANCE, $request->JOUR_NAISSANCE, $request->ANNEE_NAISSANCE)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreur de validation',
                        'errors' => ['date_naissance' => ['La date de naissance est invalide']]
                    ], 422);
                }
            }

            $naissance->update($request->all());
            $naissance->load([
                'region', 'district', 'commune', 'fokontany',
                'professionMere', 'professionPere', 
                'nationaliteMere', 'nationalitePere'
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Naissance mise à jour avec succès',
                'data' => $naissance
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Erreur mise à jour naissance', [
                'error' => $e->getMessage(),
                'naissance_id' => $id,
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour de la naissance',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * Supprimer une naissance
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $naissance = Naissance::findOrFail($id);
            $naissance->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Naissance supprimée avec succès',
                'id' => $id
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Erreur suppression naissance', [
                'error' => $e->getMessage(),
                'naissance_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression de la naissance',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * Statistiques avancées des naissances
     */
    public function statistiques(Request $request): JsonResponse
    {
        try {
            $query = Naissance::query();

            // 🎛️ Filtres pour les statistiques
            if ($request->filled('annee')) {
                $query->where('ANNEE_NAISSANCE', $request->annee);
            }
            if ($request->filled('region_id')) {
                $query->where('region_id', $request->region_id);
            }
            if ($request->filled('district_id')) {
                $query->where('district_id', $request->district_id);
            }
            if ($request->filled('sexe')) {
                $query->where('SEXE_ENFANT', $request->sexe);
            }

            $stats = [
                // 📊 Totaux
                'total_naissances' => (clone $query)->count(),
                'naissances_vivantes' => (clone $query)->where('NAISS_VIV_MORT_NE', 1)->count(),
                'mort_nes' => (clone $query)->where('NAISS_VIV_MORT_NE', 2)->count(),
                
                // ⚥ Répartition par sexe
                'par_sexe' => (clone $query)->selectRaw('SEXE_ENFANT, COUNT(*) as total')
                    ->whereNotNull('SEXE_ENFANT')
                    ->groupBy('SEXE_ENFANT')
                    ->get(),
                
                // 📅 Répartition temporelle
                'par_mois' => (clone $query)->selectRaw('MOIS_NAISSANCE, COUNT(*) as total')
                    ->whereNotNull('MOIS_NAISSANCE')
                    ->groupBy('MOIS_NAISSANCE')
                    ->orderBy('MOIS_NAISSANCE')
                    ->get(),
                'par_annee' => (clone $query)->selectRaw('ANNEE_NAISSANCE, COUNT(*) as total')
                    ->whereNotNull('ANNEE_NAISSANCE')
                    ->groupBy('ANNEE_NAISSANCE')
                    ->orderBy('ANNEE_NAISSANCE', 'desc')
                    ->get(),
                
                // 🏥 Assistance médicale
                'assistance_medicale' => (clone $query)->selectRaw('NAISS_ASSIS_PERS_SANTE, COUNT(*) as total')
                    ->whereNotNull('NAISS_ASSIS_PERS_SANTE')
                    ->groupBy('NAISS_ASSIS_PERS_SANTE')
                    ->get(),
                
                // 🏙️ Milieu
                'par_milieu' => (clone $query)->selectRaw('MILIEU, COUNT(*) as total')
                    ->whereNotNull('MILIEU')
                    ->groupBy('MILIEU')
                    ->get(),
                
                // 🌍 Nationalités
                'par_nationalite_mere' => (clone $query)->selectRaw('NATIONALITE_MERE, COUNT(*) as total')
                    ->whereNotNull('NATIONALITE_MERE')
                    ->groupBy('NATIONALITE_MERE')
                    ->get(),
                'par_nationalite_pere' => (clone $query)->selectRaw('NATIONALITE_PERE, COUNT(*) as total')
                    ->whereNotNull('NATIONALITE_PERE')
                    ->groupBy('NATIONALITE_PERE')
                    ->get(),
                
                // 💼 Professions
                'par_profession_mere' => (clone $query)->selectRaw('PROF_MERE_L, COUNT(*) as total')
                    ->whereNotNull('PROF_MERE_L')
                    ->groupBy('PROF_MERE_L')
                    ->orderBy('total', 'desc')
                    ->limit(10)
                    ->get(),
                'par_profession_pere' => (clone $query)->selectRaw('PROF_PERE_L, COUNT(*) as total')
                    ->whereNotNull('PROF_PERE_L')
                    ->groupBy('PROF_PERE_L')
                    ->orderBy('total', 'desc')
                    ->limit(10)
                    ->get(),
                
                // 📈 Âges moyens
                'age_moyen_mere' => (clone $query)->whereNotNull('AGE_MERE')->avg('AGE_MERE'),
                'age_moyen_pere' => (clone $query)->whereNotNull('AGE_PERE')->avg('AGE_PERE'),
                
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
            ];

            return response()->json([
                'success' => true,
                'message' => 'Statistiques des naissances récupérées avec succès',
                'data' => $stats,
                'filtres' => $request->all()
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur statistiques naissances', [
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