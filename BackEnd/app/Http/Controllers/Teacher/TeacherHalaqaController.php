<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Resources\HalaqaResource;
use App\Http\Resources\StudentResource;
use App\Models\Halaqa;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherHalaqaController extends Controller
{
    // Récupérer le Teacher connecté depuis son Account
    // Méthode réutilisée dans tous les controllers Teacher
    private function getTeacher(Request $request): Teacher
    {
        return Teacher::where('account_id', $request->user()->id)
                      ->firstOrFail();
    }

    // GET /api/teacher/halaqat
    // Retourne SEULEMENT les halaqat de ce teacher
    // Pas toutes les halaqat du système
    public function index(Request $request): JsonResponse
    {
        $teacher = $this->getTeacher($request);

        $halaqat = Halaqa::where('teacher_id', $teacher->id)
                         ->where('is_active', true)
                         ->withCount('students')
                         ->with('students')
                         ->get();

        return response()->json(
            HalaqaResource::collection($halaqat)
        );
    }

    // GET /api/teacher/halaqat/{halaqa}/students
    // Liste des étudiants dans une halaqa du teacher
    public function students(Request $request, Halaqa $halaqa): JsonResponse
    {
        $teacher = $this->getTeacher($request);

        // Vérifier que cette halaqa appartient à CE teacher
        // Pas à un autre teacher
        if ($halaqa->teacher_id !== $teacher->id) {
            return response()->json([
                'message' => 'هذه الحلقة ليست من حلقاتك',
            ], 403);
        }

        $students = $halaqa->students()
                           ->orderBy('full_name')
                           ->get();

        return response()->json(
            StudentResource::collection($students)
        );
    }

    // GET /api/teacher/next-seance
    // Retourne la prochaine séance avec classroom info
    // Pour le dashboard teacher "où est ma prochaine classe ?"
    public function nextSeance(Request $request): JsonResponse
    {
        $teacher = $this->getTeacher($request);

        $nextSeance = $teacher->getNextSeance();

        if (!$nextSeance) {
            return response()->json([
                'message' => 'لا توجد جلسات قادمة',
                'seance'  => null,
            ]);
        }

        return response()->json([
            'seance' => [
                'id'   => $nextSeance->id,
               'date' => $nextSeance->dateEntry?->date_value?->format('Y-m-d'),  
              'halaqa' => [
                    'id'   => $nextSeance->halaqa->id,
                    'name' => $nextSeance->halaqa->name,
                    'schedule' => $nextSeance->halaqa->schedule,
                ],
                'classroom' => $nextSeance->classroom ? [
                    'id'       => $nextSeance->classroom->id,
                    'name'     => $nextSeance->classroom->name,
                    'building' => $nextSeance->classroom->building,
                ] : null,
                'students_count' => $nextSeance->halaqa
                                               ->students()
                                               ->count(),
            ],
        ]);
    }
}