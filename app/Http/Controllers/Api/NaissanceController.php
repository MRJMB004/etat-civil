<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Naissance;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class NaissanceController extends Controller
{
    /**
     * Liste toutes les naissances avec pagination et filtres
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Naissance::avecRelations();

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
            
            // Validation du tri
            $allowedSortColumns = ['created_at', 'updated_at', 'ANNEE_NAISSANCE', 'N_ACTE'];
            if (in_array($sortBy, $allowedSortColumns)) {
                $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
            }

            // Pagination
            $perPage = min($request->get('per_page', 15), 100);
            $naissances = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Liste des naissances récupérée avec succès',
                'data' => $naissances
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la récupération des naissances', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des naissances',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * Créer une nouvelle naissance
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
                'profession_pere_id' => 'nullable|exists:professions,id',
                'profession_mere_id' => 'nullable|exists:professions,id',
                'profession_declarant_id' => 'nullable|exists:professions,id',
                'nationalite_enfant_id' => 'nullable|exists:nationalites,id',
                'ANNEE_NAISSANCE' => 'required|integer|min:1900|max:' . date('Y'),
                'MOIS_NAISSANCE' => 'required|integer|between:1,12',
                'JOUR_NAISSANCE' => 'required|integer|between:1,31',
                'SEXE_ENFANT' => 'required|integer|in:1,2',
                'N_ACTE' => 'nullable|integer|unique:naissance_2020_24,N_ACTE',
                'NOM_ENFANT' => 'required|string|max:255',
                'PRENOM_ENFANT' => 'required|string|max:255',
                'DATE_NAISSANCE_ENFANT' => 'required|date',
                'LIEU_NAISSANCE_ENFANT' => 'required|string|max:255',
                'NOM_PERE' => 'nullable|string|max:255',
                'NOM_MERE' => 'nullable|string|max:255',
            ], [
                'ANNEE_NAISSANCE.required' => 'L\'année de naissance est obligatoire',
                'MOIS_NAISSANCE.required' => 'Le mois de naissance est obligatoire',
                'JOUR_NAISSANCE.required' => 'Le jour de naissance est obligatoire',
                'SEXE_ENFANT.required' => 'Le sexe de l\'enfant est obligatoire',
                'NOM_ENFANT.required' => 'Le nom de l\'enfant est obligatoire',
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
            $annee = $request->ANNEE_NAISSANCE;
            $mois = $request->MOIS_NAISSANCE;
            $jour = $request->JOUR_NAISSANCE;

            if (!checkdate($mois, $jour, $annee)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => ['date' => ['La date de naissance est invalide']]
                ], 422);
            }

            $naissance = Naissance::create($request->all());
            $naissance->load([
                'region', 'district', 'commune', 'fokontany',
                'professionPere', 'professionMere', 'professionDeclarant', 'nationaliteEnfant'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Naissance enregistrée avec succès',
                'data' => $naissance
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de l\'enregistrement de la naissance', [
                'error' => $e->getMessage(),
                'request' => $request->all()
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
     * 
     * @param int $id
     * @return JsonResponse
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
     * 
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $naissance = Naissance::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'region_id' => 'nullable|exists:regions,id',
                'district_id' => 'nullable|exists:districts,id',
                'commune_id' => 'nullable|exists:communes,id',
                'fokontany_id' => 'nullable|exists:fokontany,id',
                'profession_pere_id' => 'nullable|exists:professions,id',
                'profession_mere_id' => 'nullable|exists:professions,id',
                'profession_declarant_id' => 'nullable|exists:professions,id',
                'nationalite_enfant_id' => 'nullable|exists:nationalites,id',
                'ANNEE_NAISSANCE' => 'sometimes|required|integer|min:1900|max:' . date('Y'),
                'MOIS_NAISSANCE' => 'sometimes|required|integer|between:1,12',
                'JOUR_NAISSANCE' => 'sometimes|required|integer|between:1,31',
                'SEXE_ENFANT' => 'sometimes|required|integer|in:1,2',
                'N_ACTE' => 'nullable|integer|unique:naissance_2020_24,N_ACTE,' . $id,
                'NOM_ENFANT' => 'sometimes|required|string|max:255',
                'PRENOM_ENFANT' => 'sometimes|required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validation de la date si fournie
            if ($request->has(['ANNEE_NAISSANCE', 'MOIS_NAISSANCE', 'JOUR_NAISSANCE'])) {
                $annee = $request->ANNEE_NAISSANCE;
                $mois = $request->MOIS_NAISSANCE;
                $jour = $request->JOUR_NAISSANCE;

                if (!checkdate($mois, $jour, $annee)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Erreur de validation',
                        'errors' => ['date' => ['La date de naissance est invalide']]
                    ], 422);
                }
            }

            $naissance->update($request->all());
            $naissance->load([
                'region', 'district', 'commune', 'fokontany',
                'professionPere', 'professionMere', 'professionDeclarant', 'nationaliteEnfant'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Naissance mise à jour avec succès',
                'data' => $naissance
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la mise à jour de la naissance', [
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
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $naissance = Naissance::findOrFail($id);
            $naissance->delete();

            return response()->json([
                'success' => true,
                'message' => 'Naissance supprimée avec succès'
            ], 200);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la suppression de la naissance', [
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
}