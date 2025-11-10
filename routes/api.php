<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DecesController;
use App\Http\Controllers\Api\NaissanceController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\DistrictController;
use App\Http\Controllers\Api\StatistiqueController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route de test
Route::get('/test', function () {
    return response()->json([
        'success' => true,
        'message' => 'API État Civil fonctionne correctement !',
        'version' => '1.0.0',
        'timestamp' => now()->toDateTimeString()
    ]);
});

// ========================================
// ROUTES POUR LES DÉCÈS
// ========================================
Route::prefix('deces')->group(function () {
    Route::get('/', [DecesController::class, 'index']); // Liste avec filtres
    Route::post('/', [DecesController::class, 'store']); // Créer
    Route::get('/{id}', [DecesController::class, 'show']); // Détails
    Route::put('/{id}', [DecesController::class, 'update']); // Mettre à jour
    Route::delete('/{id}', [DecesController::class, 'destroy']); // Supprimer
});

// ========================================
// ROUTES POUR LES NAISSANCES
// ========================================
Route::prefix('naissances')->group(function () {
    Route::get('/', [NaissanceController::class, 'index']); // Liste avec filtres
    Route::post('/', [NaissanceController::class, 'store']); // Créer
    Route::get('/{id}', [NaissanceController::class, 'show']); // Détails
    Route::put('/{id}', [NaissanceController::class, 'update']); // Mettre à jour
    Route::delete('/{id}', [NaissanceController::class, 'destroy']); // Supprimer
});

// ========================================
// ROUTES POUR LES RÉGIONS
// ========================================
Route::prefix('regions')->group(function () {
    Route::get('/', [RegionController::class, 'index']); // Liste
    Route::get('/{id}', [RegionController::class, 'show']); // Détails
    Route::get('/{id}/districts', [RegionController::class, 'districts']); // Districts d'une région
    Route::get('/{id}/statistiques', [RegionController::class, 'statistiques']); // Stats d'une région
});

// ========================================
// ROUTES POUR LES DISTRICTS
// ========================================
Route::prefix('districts')->group(function () {
    Route::get('/', [DistrictController::class, 'index']); // Liste
    Route::get('/{id}', [DistrictController::class, 'show']); // Détails
    Route::get('/{id}/communes', [DistrictController::class, 'communes']); // Communes d'un district
    Route::get('/{id}/statistiques', [DistrictController::class, 'statistiques']); // Stats d'un district
});

// ========================================
// ROUTES POUR LES STATISTIQUES
// ========================================
Route::prefix('statistiques')->group(function () {
    Route::get('/dashboard', [StatistiqueController::class, 'dashboard']); // Dashboard complet
    Route::get('/deces-par-annee', [StatistiqueController::class, 'decesParAnnee']); // Décès/année
    Route::get('/naissances-par-annee', [StatistiqueController::class, 'naissancesParAnnee']); // Naissances/année
    Route::get('/pyramide-ages', [StatistiqueController::class, 'pyramideAges']); // Pyramide des âges
    Route::get('/causes-deces', [StatistiqueController::class, 'causesDeces']); // Causes fréquentes
    Route::get('/taux-natalite', [StatistiqueController::class, 'tauxNatalite']); // Natalité/région
    Route::get('/taux-mortalite', [StatistiqueController::class, 'tauxMortalite']); // Mortalité/région
});