<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Deces;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class DecesController extends Controller
{
    /**
     * Liste tous les décès avec pagination et filtres
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Deces::avecRelations();

            // Filtre de recherche
            if ($request->filled('search')) {
                $query->recherche($request->search);
            }

            // Filtre par année
            if ($request->filled('annee')) {
                $query->parAnnee($request->annee);
            }

            // Filtre par région
            if ($request->filled('region_id')) {
                $query->parRegion($request->region_id);
            }

            // Filtre par district
            if ($request->filled('district_id')) {
                $query->parDistrict($request->district_id);
            }

            // Filtre par commune
            if ($request->filled('commune_id')) {
                $query->parCommune($request->commune_id);
            }

            // Filtre par sexe
            if ($request->filled('sexe')) {
                $query->parSexe($request->sexe);
            }

            // Tri
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            
            // Validation du tri pour éviter les injections SQL
            $allowedSortColumns = ['created_at', 'updated_at', 'ANNEE_DECES', 'N_ACTE'];
            if (in_array($sortBy, $allowedSortColumns)) {
                $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
            }

            // Pagination
            $perPage = min($request->get('per_page', 15), 100); // Limite à 100 max
            $deces = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Liste des décès récupérée avec succès',
                'data' => $deces
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la récupération des décès', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des décès',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * Créer un nouveau décès
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'region_id' => 'nullable|exists:regions,id',
                'district_id' => 'nullable|exists:districts,id',
                'commune_id' => 'nullable|exists:communes,id',
                'fokontany_id' => 'nullable|exists:fokontany,id',
                'cause_deces_id' => 'nullable|exists:causes_deces,id',
                'profession_defunt_id' => 'nullable|exists:professions,id',
                'profession_declarant_id' => 'nullable|exists:professions,id',
                'nationalite_id' => 'nullable|exists:nationalites,id',
                'ANNEE_DECES' => 'required|integer|min:1900|max:' . date('Y'),
                'MOIS_DECES' => 'required|integer|between:1,12',
                'JOUR_DECES' => 'required|integer|between:1,31',
                'SEXE_DEFUNT' => 'required|integer|in:1,2',
                'N_ACTE' => 'nullable|integer|unique:deces_2020_24,N_ACTE',
            ], [
                'ANNEE_DECES.required' => 'L\'année de décès est obligatoire',
                'MOIS_DECES.required' => 'Le mois de décès est obligatoire',
                'JOUR_DECES.required' => 'Le jour de décès est obligatoire',
                'SEXE_DEFUNT.required' => 'Le sexe du défunt est obligatoire',
                'N_ACTE.unique' => 'Le numéro d\'acte existe déjà',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validation de la date
            $annee = $request->ANNEE_DECES;
            $mois = $request->MOIS_DECES;
            $jour = $request->JOUR_DECES;

            if (!checkdate($mois, $jour, $annee)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => ['date' => ['La date de décès est invalide']]
                ], 422);
            }

            $deces = Deces::create($request->all());
            $deces->load([
                'region', 'district', 'commune', 'fokontany',
                'causeDeces', 'professionDefunt', 'professionDeclarant', 'nationalite'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Décès enregistré avec succès',
                'data' => $deces
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'enregistrement du décès', [
                'error' => $e->getMessage(),
                'request' => $request->all()
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
     * 
     * @param int $id
     * @return JsonResponse
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
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $deces = Deces::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'region_id' => 'nullable|exists:regions,id',
                'district_id' => 'nullable|exists:districts,id',
                'commune_id' => 'nullable|exists:communes,id',
                'fokontany_id' => 'nullable|exists:fokontany,id',
                'cause_deces_id' => 'nullable|exists:causes_deces,id',
                'profession_defunt_id' => 'nullable|exists:professions,id',
                'profession_declarant_id' => 'nullable|exists:professions,id',
                'nationalite_id' => 'nullable|exists:nationalites,id',
                'ANNEE_DECES' => 'sometimes|required|integer|min:1900|max:' . date('Y'),
                'MOIS_DECES' => 'sometimes|required|integer|between:1,12',
                'JOUR_DECES' => 'sometimes|required|integer|between:1,31',
                'SEXE_DEFUNT' => 'sometimes|required|integer|in:1,2',
                'N_ACTE' => 'nullable|integer|unique:deces_2020_24,N_ACTE,' . $id,
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validation de la date si fournie
            if ($request->has(['ANNEE_DECES', 'MOIS_DECES', 'JOUR_DECES'])) {
                $annee = $request->ANNEE_DECES;
                $mois = $request->MOIS_DECES;
                $jour = $request->JOUR_DECES;

                if (!checkdate($mois, $jour, $annee)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreur de validation',
                        'errors' => ['date' => ['La date de décès est invalide']]
                    ], 422);
                }
            }

            $deces->update($request->all());
            $deces->load([
                'region', 'district', 'commune', 'fokontany',
                'causeDeces', 'professionDefunt', 'professionDeclarant', 'nationalite'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Décès mis à jour avec succès',
                'data' => $deces
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la mise à jour du décès', [
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
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deces = Deces::findOrFail($id);
            $deces->delete();

            return response()->json([
                'success' => true,
                'message' => 'Décès supprimé avec succès'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la suppression du décès', [
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
}