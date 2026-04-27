<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreAttendanceRequest;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Models\Seance;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherAttendanceController extends Controller
{
    private function getTeacher(Request $request): Teacher
    {
        return Teacher::where('account_id', $request->user()->id)
                      ->firstOrFail();
    }

    // GET /api/teacher/seances/{seance}/attendance
    // Voir la liste de présence d'une séance
    public function index(Request $request, Seance $seance): JsonResponse
    {
        $teacher = $this->getTeacher($request);

        $attendances = $seance->attendances()
                              ->with('student')
                              ->get();

        return $this->apiSuccess(
            AttendanceResource::collection($attendances)
        );
    }

    // POST /api/teacher/seances/{seance}/attendance
    // Enregistrer la présence de tous les étudiants d'une séance
    // L'Observer AttendanceObserver se déclenche automatiquement
    // pour mettre à jour le score de chaque étudiant
    public function store(
        StoreAttendanceRequest $request,
        Seance $seance
    ): JsonResponse {
        $teacher = $this->getTeacher($request);

        // Transaction = si une présence échoue → tout annuler
        // Évite les enregistrements partiels
        $attendances = DB::transaction(function () use ($request, $seance, $teacher) {
            $result = [];

            foreach ($request->records as $record) {
                // updateOrCreate = créer ou mettre à jour si déjà existe
                // Permet de corriger une présence déjà enregistrée
                $attendance = Attendance::updateOrCreate(
                    [
                        // Clé unique: un étudiant par séance
                        'seance_id'  => $seance->id,
                        'student_id' => $record['student_id'],
                    ],
                    [
                        'status'            => $record['status'],
                        'evaluation_grade'  => $record['evaluation_grade']  ?? null,
                        'points' => $record['points'] ?? 0,
                    ]
                );

                // L'Observer AttendanceObserver se déclenche ici
                // automatiquement pour recalculer le score ranking

                $result[] = $attendance->load('student');
            }

            return $result;
        });

        return $this->apiSuccess(
            AttendanceResource::collection(
                collect($attendances)
            ),
            'تم تسجيل الحضور بنجاح',
            201
        );
    }

    // PUT /api/teacher/attendance/{attendance}
    // Corriger une seule présence
    public function update(Request $request, Attendance $attendance): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:present,absent,late,excused',
        ]);
    
        $teacher = $this->getTeacher($request);
        
        $attendance = Attendance::where('id', $attendance->id)
            ->whereHas('seance.halaqa', function ($query) use ($teacher) {
                $query->where('teacher_id', $teacher->id);
            })
            ->first();
        
        if (!$attendance) {
            return response()->json([
                'message' => 'Présence non trouvée ou accès non autorisé'
            ], 404);
        }
    
        $attendance->update([
            'status' => $request->status,
        ]);
    
        return $this->apiSuccess(
            new AttendanceResource($attendance->load('student'))
        );
    }    
}