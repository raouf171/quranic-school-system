<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreRevisionRequest;
use App\Http\Requests\Teacher\UpdateRevisionRequest;
use App\Http\Resources\RevisionResource;
use App\Models\Revision;
use App\Models\Seance;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherRevisionController extends Controller
{
    private function getTeacher(Request $request): Teacher
    {
        return Teacher::where('account_id', $request->user()->id)
            ->firstOrFail();
    }

    // GET /api/teacher/seances/{seance}/revisions
    public function index(Request $request, Seance $seance): JsonResponse
    {
        $teacher = $this->getTeacher($request);

        if ($seance->halaqa->teacher_id !== $teacher->id) {
            return response()->json([
                'message' => 'Vous n\'avez pas accès à cette séance',
            ], 403);
        }

        $revisions = $seance->revisions()
            ->with(['student', 'evaluation'])
            ->get();

        return $this->apiSuccess(
            RevisionResource::collection($revisions)
        );
    }

    // POST /api/teacher/seances/{seance}/revisions
    public function store(
        StoreRevisionRequest $request,
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
                'message' => 'الطالب لا ينتمي إلى حلقة هذه الجلسة',
            ], 422);
        }

        $revision = Revision::create([
            'seance_id' => $seance->id,
            'student_id' => $request->student_id,
            'evaluation_id' => $request->evaluation_id,
            'surah_start' => $request->surah_start,
            'surah_end' => $request->surah_end,
            'verse_start' => $request->verse_start,
            'verse_end' => $request->verse_end,
        ]);

        $revision->load(['student', 'evaluation']);

        return $this->apiSuccess(
            new RevisionResource($revision),
            null,
            201
        );
    }

    // PUT/PATCH /api/teacher/seances/{seance}/revisions/{revision}
    public function update(
        UpdateRevisionRequest $request,
        Seance $seance,
        Revision $revision
    ): JsonResponse {
        if ($response = $this->teacherMayAccessSeance($request, $seance)) {
            return $response;
        }

        if (! $this->recordBelongsToSeance($revision->seance_id, $seance->id)) {
            return response()->json([
                'message' => 'سجل المراجعة لا يتبع هذه الجلسة',
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

        $revision->update($payload);

        $revision->load(['student', 'evaluation']);

        return $this->apiSuccess(new RevisionResource($revision));
    }

    // DELETE /api/teacher/seances/{seance}/revisions/{revision}
    public function destroy(Request $request, Seance $seance, Revision $revision): JsonResponse
    {
        if ($response = $this->teacherMayAccessSeance($request, $seance)) {
            return $response;
        }

        if (! $this->recordBelongsToSeance($revision->seance_id, $seance->id)) {
            return response()->json([
                'message' => 'سجل المراجعة لا يتبع هذه الجلسة',
            ], 404);
        }

        $revision->delete();

        return $this->apiSuccess(null, 'تم حذف سجل المراجعة');
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
