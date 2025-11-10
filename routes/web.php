<?php

use App\Http\Controllers\DecesController;
use App\Http\Controllers\NaissanceController;

Route::resource('deces', DecesController::class);
Route::resource('naissances', NaissanceController::class);

// Routes pour les statistiques
Route::get('/statistiques/deces', [DecesController::class, 'statistiques']);
Route::get('/statistiques/naissances', [NaissanceController::class, 'statistiques']);
Route::get('/dashboard', [DecesController::class, 'dashboard']);