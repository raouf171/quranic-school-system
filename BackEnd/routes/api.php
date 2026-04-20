<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AdminStudentController;
use App\Http\Controllers\Admin\AdminHalaqaController;
use App\Http\Controllers\Admin\AdminTeacherController;
use App\Http\Controllers\Teacher\TeacherHalaqaController;
use App\Http\Controllers\Teacher\TeacherSeanceController;
use App\Http\Controllers\Teacher\TeacherAttendanceController;
use App\Http\Controllers\Teacher\TeacherMemorizationController;
use App\Http\Controllers\Teacher\TeacherRevisionController;
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

    // ── ADMIN ─────────────────────────────
    Route::middleware('role:admin')
         ->prefix('admin')
         ->group(function () {
        Route::apiResource('students', AdminStudentController::class);
        Route::apiResource('halaqat',  AdminHalaqaController::class);
        Route::get('halaqat/{halaqa}/students', [AdminHalaqaController::class, 'students']);
        Route::get('teachers',           [AdminTeacherController::class, 'index']);
        Route::get('teachers/{teacher}', [AdminTeacherController::class, 'show']);
        Route::put('teachers/{teacher}', [AdminTeacherController::class, 'update']);
    });

    // ── TEACHER ───────────────────────────
    Route::middleware('role:teacher')
         ->prefix('teacher')
         ->group(function () {

        // Halaqat du teacher
        Route::get('halaqat',
            [TeacherHalaqaController::class, 'index']);
        Route::get('halaqat/{halaqa}/students',
            [TeacherHalaqaController::class, 'students']);

        // Prochaine séance + salle
        Route::get('next-seance',
            [TeacherHalaqaController::class, 'nextSeance']);

        // Séances
        Route::get('halaqat/{halaqa}/seances',
            [TeacherSeanceController::class, 'index']);
        Route::post('halaqat/{halaqa}/seances',
            [TeacherSeanceController::class, 'store']);
        Route::get('seances/{seance}',
            [TeacherSeanceController::class, 'show']);

        // Présence
        Route::get('seances/{seance}/attendance',
            [TeacherAttendanceController::class, 'index']);
        Route::post('seances/{seance}/attendance',
            [TeacherAttendanceController::class, 'store']);
        Route::put('attendance/{attendance}',
            [TeacherAttendanceController::class, 'update']);

        // Mémorisation (hifz)
        Route::get('seances/{seance}/memorizations',
            [TeacherMemorizationController::class, 'index']);
        Route::post('seances/{seance}/memorizations',
            [TeacherMemorizationController::class, 'store']);

        // Révision (muraja'ah)
        Route::get('seances/{seance}/revisions',
            [TeacherRevisionController::class, 'index']);
        Route::post('seances/{seance}/revisions',
            [TeacherRevisionController::class, 'store']);
    });

    // ── PARENT ────────────────────────────
    Route::middleware('role:parent')
         ->prefix('parent')
         ->group(function () {
        // Jour 4
    });

    // ── TOUS LES RÔLES ────────────────────
    Route::get('/evaluations', function () {
        return response()->json(\App\Models\Evaluation::all());
    });
});