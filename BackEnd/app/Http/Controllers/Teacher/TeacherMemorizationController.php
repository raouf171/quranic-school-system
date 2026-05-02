<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreMemorizationRequest;
use App\Http\Requests\Teacher\UpdateMemorizationRequest;
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
        if ($response = $this->teacherMayAccessSeance($request, $seance)) {
            return $response;
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

    // PUT/PATCH /api/teacher/seances/{seance}/memorizations/{memorization}
    public function update(
        UpdateMemorizationRequest $request,
        Seance $seance,
        Memorization $memorization
    ): JsonResponse {
        if ($response = $this->teacherMayAccessSeance($request, $seance)) {
            return $response;
        }

        if (! $this->recordBelongsToSeance($memorization->seance_id, $seance->id)) {
            return response()->json([
                'message' => 'سجل الحفظ لا يتبع هذه الجلسة',
            ], 404);
        }

        $data = $request->validated();
        $studentBelongsToSeanceHalaqa = Student::where('id', $data['student_id'])
            ->where('halaqa_id', $seance->halaqa_id)
            ->exists();

        if (! $studentBelongsToSeanceHalaqa) {
            return response()->json([
                'message' => 'الطالب لا ينتمي إلى حلقة هذه الجلسة',
            ], 422);
        }

        $payload = [
            'student_id' => $data['student_id'],
            'evaluation_id' => $data['evaluation_id'],
            'surah_start' => $data['surah_start'],
            'verse_start' => $data['verse_start'],
            'surah_end' => $data['surah_end'],
            'verse_end' => $data['verse_end'],
        ];
        if (array_key_exists('points', $data)) {
            $payload['points'] = $data['points'];
        }

        $memorization->update($payload);

        $memorization->load(['student', 'evaluation']);

        return $this->apiSuccess(new MemorizationResource($memorization));
    }

    // DELETE /api/teacher/seances/{seance}/memorizations/{memorization}
    public function destroy(Request $request, Seance $seance, Memorization $memorization): JsonResponse
    {
        if ($response = $this->teacherMayAccessSeance($request, $seance)) {
            return $response;
        }

        if (! $this->recordBelongsToSeance($memorization->seance_id, $seance->id)) {
            return response()->json([
                'message' => 'سجل الحفظ لا يتبع هذه الجلسة',
            ], 404);
        }

        $memorization->delete();

        return $this->apiSuccess(null, 'تم حذف سجل الحفظ');
    }

    private function teacherMayAccessSeance(Request $request, Seance $seance): ?JsonResponse
    {
        $teacher = $this->getTeacher($request);

        if ($seance->halaqa->teacher_id !== $teacher->id) {
            return response()->json([
                'message' => 'ليس لديك صلاحية الوصول إلى هذه الجلسة',
            ], 403);
        }

        return null;
    }

    private function recordBelongsToSeance(int $recordSeanceId, int $seanceId): bool
    {
        return $recordSeanceId === $seanceId;
    }
}