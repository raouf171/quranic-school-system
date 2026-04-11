<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// ROUTES PUBLIQUES —' aucun token requis'

Route::post('/login', [AuthController::class, 'login']);

//hado ta7t ga3 protege par token

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // ── ADMIN UNIQUEMENT ──────────────────────────────
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        
    });

    
    // Route de test temporaire — supprimer après test
Route::middleware(['auth:sanctum', 'role:admin'])->get('/test-admin-only', function () {
    return response()->json(['message' => 'Tu es admin ✅']);
});

    // ── TEACHER UNIQUEMENT ────────────────────────────
    Route::middleware('role:teacher')->prefix('teacher')->group(function () {
    });

    // ── PARENT UNIQUEMENT ─────────────────────────────
    Route::middleware('role:parent')->prefix('parent')->group(function () {
    });




































    // ── ACCESSIBLE PAR TOUS LES RÔLES ─────────────────
    // Table de référence — grades d'évaluation
    // L'app mobile charge ça une fois au démarrage
    // Route::get('/evaluations', [EvaluationController::class, 'index']);
});