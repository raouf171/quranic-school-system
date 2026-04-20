<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreMemorizationRequest;
use App\Http\Resources\MemorizationResource;
use App\Models\Memorization;
use App\Models\Seance;
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
    public function index(Seance $seance): JsonResponse
    {
        $memorizations = $seance->memorizations()
                                ->with(['student', 'evaluation'])
                                ->get();

        return response()->json(
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

        $memorization = Memorization::create([
            'seance_id'     => $seance->id,
            'student_id'    => $request->student_id,
            'evaluation_id' => $request->evaluation_id,
            'surah_start'   => $request->surah_start,
            'verse_start'   => $request->verse_start,
            'surah_end'     => $request->surah_end,
            'verse_end'     => $request->verse_end,
            // evaluation_grade et evaluation_points seront
            // remplis automatiquement par MemorizationObserver
        ]);

        $memorization->load(['student', 'evaluation']);

        return response()->json(
            new MemorizationResource($memorization),
            201
        );
    }
}