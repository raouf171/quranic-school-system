<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSeanceRequest;
use App\Http\Requests\Admin\UpdateSeanceRequest;
use App\Http\Resources\SeanceResource;
use App\Models\DateEntry;
use App\Models\Halaqa;
use App\Models\Seance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSeanceController extends Controller
{
    // GET /api/admin/seances
    public function index(Request $request): JsonResponse
    {
        $query = Seance::query()
            ->with(['halaqa', 'classroom', 'dateEntry', 'teacher'])
            ->join('dates', 'seances.date_id', '=', 'dates.id')
            ->orderBy('dates.date_value', 'desc')
            ->select('seances.*');

        if ($request->filled('halaqa_id')) {
            $query->where('seances.halaqa_id', $request->integer('halaqa_id'));
        }

        return $this->apiSuccess(
            SeanceResource::collection($query->paginate(15))
        );
    }

    // POST /api/admin/seances
    public function store(StoreSeanceRequest $request): JsonResponse
    {
        $halaqa = Halaqa::findOrFail($request->integer('halaqa_id'));

        if (!$halaqa->teacher_id) {
            return response()->json([
                'message' => 'لا يمكن إنشاء جلسة دون أستاذ مرتبط بهذه الحلقة',
            ], 422);
        }

        $dateEntry = DateEntry::firstOrCreate(
            ['date_value' => $request->date],
            ['created_by' => $halaqa->teacher_id]
        );

        $seance = Seance::updateOrCreate(
            [
                'halaqa_id' => $halaqa->id,
                'date_id' => $dateEntry->id,
            ],
            [
                'created_by' => $halaqa->teacher_id,
                'classroom_id' => $request->classroom_id,
                'notes' => $request->notes,
            ]
        );

        $seance->load(['halaqa', 'classroom', 'dateEntry', 'teacher']);

        return $this->apiSuccess(
            new SeanceResource($seance),
            null,
            201
        );
    }

    // GET /api/admin/seances/{seance}
    public function show(Seance $seance): JsonResponse
    {
        $seance->load(['halaqa', 'classroom', 'dateEntry', 'teacher']);

        return $this->apiSuccess(new SeanceResource($seance));
    }

    // PUT /api/admin/seances/{seance}
    public function update(UpdateSeanceRequest $request, Seance $seance): JsonResponse
    {
        if ($request->has('date')) {
            $halaqa = $seance->halaqa()->first();
            $teacherId = $halaqa?->teacher_id ?? $seance->created_by;

            if (!$teacherId) {
                return response()->json([
                    'message' => 'لا يمكن تحديث تاريخ الجلسة دون أستاذ مرتبط',
                ], 422);
            }

            $dateEntry = DateEntry::firstOrCreate(
                ['date_value' => $request->date],
                ['created_by' => $teacherId]
            );

            $seance->date_id = $dateEntry->id;
        }

        if ($request->has('classroom_id')) {
            $seance->classroom_id = $request->input('classroom_id');
        }

        if ($request->has('notes')) {
            $seance->notes = $request->input('notes');
        }

        $seance->save();
        $seance->load(['halaqa', 'classroom', 'dateEntry', 'teacher']);

        return $this->apiSuccess(
            new SeanceResource($seance),
            'تم تحديث الجلسة بنجاح'
        );
    }

    // DELETE /api/admin/seances/{seance}
    public function destroy(Seance $seance): JsonResponse
    {
        $seance->delete();

        return $this->apiSuccess(
            null,
            'تم حذف الجلسة بنجاح'
        );
    }
}
