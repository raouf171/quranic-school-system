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
    public function index(Request $request, Seance $seance): JsonResponse
{
    $teacher = $this->getTeacher($request);
    
    // ✅ Verify teacher owns this seance
    if ($seance->halaqa->teacher_id !== $teacher->id) {
        return response()->json([
            'message' => 'Vous n\'avez pas accès à cette séance'
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
        $teacher = $this->getTeacher($request);
        
        // ✅ Verify teacher owns this seance
        if ($seance->halaqa->teacher_id !== $teacher->id) {
            return response()->json([
                'message' => 'Vous ne pouvez pas ajouter des révisions à une séance qui ne vous appartient pas'
            ], 403);
        }
        
        $revision = Revision::create([
            'seance_id'     => $seance->id,
            'student_id'    => $request->student_id,
            'evaluation_id' => $request->evaluation_id,
            'surah_start'   => $request->surah_start,
            'surah_end'     => $request->surah_end,    
            'verse_start'   => $request->verse_start,
            'verse_end'     => $request->verse_end,
        ]);
    
        $revision->load(['student', 'evaluation']);
    
        return $this->apiSuccess(
            new RevisionResource($revision),
            null,
            201
        );
    } }

        
    
