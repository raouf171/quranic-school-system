<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreRevisionRequest;
use App\Http\Resources\RevisionResource;
use App\Models\Revision;
use App\Models\Seance;
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
    public function index(Seance $seance): JsonResponse
    {
        $revisions = $seance->revisions()
                            ->with(['student', 'evaluation'])
                            ->get();

        return response()->json(
            RevisionResource::collection($revisions)
        );
    }

    // POST /api/teacher/seances/{seance}/revisions
    public function store(
        StoreRevisionRequest $request,
        Seance $seance
    ): JsonResponse {
        $teacher = $this->getTeacher($request);

        $revision = Revision::create([
            'seance_id'     => $seance->id,
            'student_id'    => $request->student_id,
            'evaluation_id' => $request->evaluation_id,
            'surah_start' => $request->surah_start,
            'surah_end'   => $request->surah_end,    
            'verse_start'   => $request->verse_start,
            'verse_end'     => $request->verse_end,
        ]);

        $revision->load(['student', 'evaluation']);

        return response()->json(
            new RevisionResource($revision),
            201
        );
    }
}