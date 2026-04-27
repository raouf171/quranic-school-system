<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\TeacherResource;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminTeacherController extends Controller
{
    // GET /api/admin/teachers
    public function index(): JsonResponse
    {
        $teachers = Teacher::with(['account', 'halaqat'])
                           ->withCount('halaqat')
                           ->get();

        return $this->apiSuccess(
            TeacherResource::collection($teachers)
        );
    }

    // GET /api/admin/teachers/{teacher}
    public function show(Teacher $teacher): JsonResponse
    {
        $teacher->load(['account', 'halaqat.students']);

        return $this->apiSuccess(new TeacherResource($teacher));
    }

    // PUT /api/admin/teachers/{teacher}
    public function update(Request $request, Teacher $teacher): JsonResponse
{
    $validated = $request->validate([
        'name'         => 'sometimes|string|max:100',
        'hiring_date'  => 'sometimes|nullable|date',
        'is_available' => 'sometimes|boolean',
    ]);

    $teacher->update($validated);

    return $this->apiSuccess(
        new TeacherResource($teacher->fresh('halaqat'))
    );
}
}