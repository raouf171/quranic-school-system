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
use App\Http\Controllers\Parent\ParentController;
use App\Http\Controllers\Admin\AdminAccountController;
use App\Http\Controllers\Admin\AdminParentController;
use App\Http\Controllers\Admin\AdminPaymentController;

use App\Http\Controllers\Admin\AdminAnnouncementController;
use App\Http\Controllers\Admin\AdminClassroomController;

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

    Route::get('students/form-enums', [AdminStudentController::class, 'formEnums']);
    Route::post('students/{student}/photo', [AdminStudentController::class, 'uploadPhoto']);
    Route::delete('students/{student}/photo', [AdminStudentController::class, 'deletePhoto']);
    Route::put('parents/{parent}', [AdminParentController::class, 'update']);
    Route::get('students/{student}/payments', [AdminPaymentController::class, 'studentPayments']);

    // Students
    Route::apiResource('students', AdminStudentController::class);

    // Halaqat
    Route::apiResource('halaqat', AdminHalaqaController::class);
    Route::get('halaqat/{halaqa}/students',
        [AdminHalaqaController::class, 'students']);

    // Teachers (lecture + update)
    Route::get('teachers',              [AdminTeacherController::class, 'index']);
    Route::get('teachers/{teacher}',    [AdminTeacherController::class, 'show']);
    Route::put('teachers/{teacher}',    [AdminTeacherController::class, 'update']);

    // Accounts — Factory Pattern
    Route::get('accounts',                    [AdminAccountController::class, 'index']);
    Route::post('accounts/teacher',           [AdminAccountController::class, 'storeTeacher']);
    Route::post('accounts/parent',            [AdminAccountController::class, 'storeParent']);
    Route::put('accounts/{account}/toggle',   [AdminAccountController::class, 'toggleActive']);

    // Payments
    Route::get('payments',                      [AdminPaymentController::class, 'index']);
    Route::post('payments',                     [AdminPaymentController::class, 'store']);
    Route::get('payments/{payment}',            [AdminPaymentController::class, 'show']);
    Route::put('payments/{payment}',            [AdminPaymentController::class, 'update']);

    // Announcements
    Route::apiResource('announcements', AdminAnnouncementController::class);

    // Classrooms
    Route::apiResource('classrooms', AdminClassroomController::class);
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

        Route::get('announcements',
            [TeacherHalaqaController::class, 'announcements']);
    });

    // ── PARENT ────────────────────────────
    Route::middleware('role:parent')
     ->prefix('parent')
     ->group(function () {

    Route::get('children',
        [ParentController::class, 'children']);

    Route::get('children/{student}/attendance',
        [ParentController::class, 'attendance']);

    Route::get('children/{student}/memorizations',
        [ParentController::class, 'memorizations']);

    Route::get('children/{student}/revisions',
        [ParentController::class, 'revisions']);

    Route::get('children/{student}/payments',
        [ParentController::class, 'payments']);

    Route::get('children/{student}/ranking',
        [ParentController::class, 'ranking']);

    Route::get('announcements',
        [ParentController::class, 'announcements']);
});
        // Jour 4
    });

    // ── TOUS LES RÔLES ────────────────────
    Route::get('/evaluations', function () {
        return response()->json([
            'data' => \App\Models\Evaluation::all(),
        ]);
    });
