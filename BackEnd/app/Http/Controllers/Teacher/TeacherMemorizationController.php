<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreMemorizationRequest;
use App\Http\Resources\MemorizationResource;
use App\Models\Memorization;
use App\Models\Seance;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherMemorizationController extends Controller
{
    private function getTeacher(Request $request): Teacher
    {
        return Teacher::where('account_id', $request->user()->id)
                      ->firstOrFail();
    }

    // GET /api/teacher/seances/{seance}/memorizations
    public function index(Request $request, Seance $seance): JsonResponse
{
    $teacher = $this->getTeacher($request);
    
    if ($seance->halaqa->teacher_id !== $teacher->id) {
        return response()->json([
            'message' => 'ليس بك صلاحية الاطلاع على هده الحلقة !'
        ], 403);
    }
    
    $memorizations = $seance->memorizations()
                            ->with(['student', 'evaluation'])
                            ->get();

    return $this->apiSuccess(
        MemorizationResource::collection($memorizations)
    );
}

    // POST /api/teacher/seances/{seance}/memorizations
    // Enregistrer le hifz d'un étudiant
    // MemorizationObserver se déclenche → met à jour score ranking
    public function store(
        StoreMemorizationRequest $request,
        Seance $seance
    ): JsonResponse {
        $teacher = $this->getTeacher($request);
        
        // ✅ Verify teacher owns this seance
        if ($seance->halaqa->teacher_id !== $teacher->id) {
            return response()->json([
                'message' => 'ليس لديط الصلاحية لإضافة معلومات إلى هذه الجلسة'
            ], 403);
        }

        $studentBelongsToSeanceHalaqa = Student::where('id', $request->student_id)
            ->where('halaqa_id', $seance->halaqa_id)
            ->exists();

        if (! $studentBelongsToSeanceHalaqa) {
            return response()->json([
                'message' => 'الطالب لا ينتمي إلى حلقة هذه الجلسة'
            ], 422);
        }
        
        $memorization = Memorization::create([
            'seance_id'     => $seance->id,
            'student_id'    => $request->student_id,
            'evaluation_id' => $request->evaluation_id,
            'surah_start'   => $request->surah_start,
            'verse_start'   => $request->verse_start,
            'surah_end'     => $request->surah_end,
            'verse_end'     => $request->verse_end,
        ]);
    
        $memorization->load(['student', 'evaluation']);
    
        return $this->apiSuccess(
            new MemorizationResource($memorization),
            null,
            201
        );
    }
}