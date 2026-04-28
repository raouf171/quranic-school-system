<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Resources\SeanceResource;
use App\Models\Halaqa;
use App\Models\Seance;
use App\Models\Teacher;
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

        return $this->apiSuccess(
            SeanceResource::collection($seances)
        );
    }

    // GET /api/teacher/seances/{seance}
    // Détail d'une séance (où et quand)
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

        $seance->load(['halaqa', 'classroom', 'dateEntry', 'teacher']);

        return $this->apiSuccess(new SeanceResource($seance));
    }
}