<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClassroomRequest;
use App\Http\Requests\Admin\UpdateClassroomRequest;
use App\Models\Classroom;
use Illuminate\Http\JsonResponse;

class AdminClassroomController extends Controller
{
    // GET /api/admin/classrooms
    public function index(): JsonResponse
    {
        $classrooms = Classroom::withCount('seances')
                               ->orderBy('name')
                               ->get();

        return response()->json($classrooms->map(fn($c) => [
            'id'            => $c->id,
            'name'          => $c->name,
            'building'      => $c->building,
            'capacity'      => $c->capacity,
            'is_available'  => $c->is_available,
            'seances_count' => $c->seances_count,
        ]));
    }

    // POST /api/admin/classrooms
    public function store(StoreClassroomRequest $request): JsonResponse
    {
        $classroom = Classroom::create($request->validated());

        return response()->json([
            'message'   => 'تم إنشاء القاعة بنجاح',
            'classroom' => $classroom,
        ], 201);
    }

    // GET /api/admin/classrooms/{classroom}
    public function show(Classroom $classroom): JsonResponse
    {
        $classroom->loadCount('seances');

        return response()->json($classroom);
    }

    // PUT /api/admin/classrooms/{classroom}
    public function update(
        UpdateClassroomRequest $request,
        Classroom $classroom
    ): JsonResponse {
        $classroom->update($request->validated());

        return response()->json([
            'message'   => 'تم تحديث القاعة',
            'classroom' => $classroom->fresh(),
        ]);
    }

    public function destroy(Classroom $classroom): JsonResponse
    {
        if ($classroom->seances()->count() > 0) {
            return response()->json([
                'message' => 'لا يمكن حذف القاعة لأنها مرتبطة بجلسات',
            ], 422);
        }

        $classroom->delete();

        return response()->json([
            'message' => 'تم حذف القاعة',
        ]);
    }
}