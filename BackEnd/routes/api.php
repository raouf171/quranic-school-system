<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AdminStudentController;
use App\Http\Controllers\Admin\AdminHalaqaController;
use App\Http\Controllers\Admin\AdminTeacherController;
use Illuminate\Support\Facades\Route;

// ══════════════════════════════════════════
// ROUTES PUBLIQUES
// ══════════════════════════════════════════
Route::post('/login', [AuthController::class, 'login']);

// ══════════════════════════════════════════
// ROUTES PROTÉGÉES
// ══════════════════════════════════════════
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);

    // ── ADMIN ─────────────────────────────────────────
    Route::middleware('role:admin')
         ->prefix('admin')
         ->name('admin.')
         ->group(function () {

        // Students — CRUD complet
        // Génère automatiquement:
        // GET    /admin/students
        // POST   /admin/students
        // GET    /admin/students/{student}
        // PUT    /admin/students/{student}
        // DELETE /admin/students/{student}
        Route::apiResource('students', AdminStudentController::class);

        // Halaqat — CRUD complet
        Route::apiResource('halaqat', AdminHalaqaController::class);

        // Route supplémentaire: étudiants d'une halaqa
        Route::get('halaqat/{halaqa}/students',
            [AdminHalaqaController::class, 'students']
        );

        // Teachers — lecture + modification seulement
        Route::get('teachers',        [AdminTeacherController::class, 'index']);
        Route::get('teachers/{teacher}', [AdminTeacherController::class, 'show']);
        Route::put('teachers/{teacher}', [AdminTeacherController::class, 'update']);
    });

    // ── TEACHER ───────────────────────────────────────
    Route::middleware('role:teacher')
         ->prefix('teacher')
         ->group(function () {
        // Sera ajouté Jour 3
    });

    // ── PARENT ────────────────────────────────────────
    Route::middleware('role:parent')
         ->prefix('parent')
         ->group(function () {
        // Sera ajouté Jour 4
    });

    // ── TOUS LES RÔLES ────────────────────────────────
    Route::get('/evaluations', function () {
        return response()->json(
            \App\Models\Evaluation::all()
        );
    });
});