<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreSeanceRequest;
use App\Http\Resources\SeanceResource;
use App\Models\Halaqa;
use App\Models\Seance;
use App\Models\Teacher;
use App\Models\DateEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherSeanceController extends Controller
{
    private function getTeacher(Request $request): Teacher
    {
        return Teacher::where('account_id', $request->user()->id)
                      ->firstOrFail();
    }

    // GET /api/teacher/halaqat/{halaqa}/seances
    // Liste les séances passées d'une halaqa
    public function index(Request $request, Halaqa $halaqa): JsonResponse
    {
        $teacher = $this->getTeacher($request);

        // Sécurité: vérifier que la halaqa appartient au teacher
        if ($halaqa->teacher_id !== $teacher->id) {
            return response()->json([
                'message' => 'هذه الحلقة ليست من حلقاتك',
            ], 403);
        }

        $seances = Seance::where('halaqa_id', $halaqa->id)
            ->join('dates', 'seances.date_id', '=', 'dates.id')
            ->orderBy('dates.date_value', 'desc')
            ->select('seances.*')
            ->with(['classroom', 'dateEntry'])
            ->paginate(10);

        return response()->json(
            SeanceResource::collection($seances)
        );
    }

    // POST /api/teacher/halaqat/{halaqa}/seances
    // Ouvrir une nouvelle séance pour aujourd'hui
    public function store(
        StoreSeanceRequest $request,
        Halaqa $halaqa
    ): JsonResponse {
        $teacher = $this->getTeacher($request);

        // Vérifier ownership
        if ($halaqa->teacher_id !== $teacher->id) {
            return response()->json([
                'message' => 'هذه الحلقة ليست من حلقاتك',
            ], 403);
        }

        $dateEntry = DateEntry::firstOrCreate(
            ['date_value' => $request->date],
            ['created_by' => $teacher->id]
        );

        // firstOrCreate = si séance existe déjà pour ce jour → retourner existante
        // Évite les doublons (unique constraint: halaqa_id + date)
        $seance = Seance::firstOrCreate(
            [
                'halaqa_id' => $halaqa->id,
                'date_id'   => $dateEntry->id,  
            ],
            [
                'created_by'   => $teacher->id,
                'classroom_id' => $request->classroom_id,
                'notes'        => $request->notes,
            ]
        );

        // Charger les relations pour la réponse
        $seance->load(['halaqat', 'classroom', 'teacher']);

        return response()->json(
            new SeanceResource($seance),
            201
        );
    }



    
    // GET /api/teacher/seances/{seance}
    // Détail d'une séance avec toutes ses données
    public function show(Request $request, Seance $seance): JsonResponse
    {
        $teacher = $this->getTeacher($request);

        // Vérifier que la séance appartient à une halaqa du teacher
        $halaqa = Halaqa::find($seance->halaqa_id);
        if (!$halaqa || $halaqa->teacher_id !== $teacher->id) {
            return response()->json([
                'message' => 'غير مصرح بالوصول لهذه الجلسة',
            ], 403);
        }

        $seance->load([
            'halaqa',
            'classroom',
            'attendances.student',
            'memorizations.student',
            'revisions.student',
        ]);

        return response()->json(new SeanceResource($seance));
    }
}