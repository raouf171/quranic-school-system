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

        return response()->json(
            TeacherResource::collection($teachers)
        );
    }

    // GET /api/admin/teachers/{teacher}
    public function show(Teacher $teacher): JsonResponse
    {
        $teacher->load(['account', 'halaqat.students']);

        return response()->json(new TeacherResource($teacher));
    }

    // PUT /api/admin/teachers/{teacher}
    public function update(Request $request, Teacher $teacher): JsonResponse
    {
        $request->validate([
            'name'         => 'sometimes|string|max:100',
            'hiring_date'  => 'sometimes|nullable|date',
            'is_available' => 'sometimes|boolean',
        ]);

        $teacher->update($request->validated());

        return response()->json(
            new TeacherResource($teacher->fresh('halaqat'))
        );
    }
}