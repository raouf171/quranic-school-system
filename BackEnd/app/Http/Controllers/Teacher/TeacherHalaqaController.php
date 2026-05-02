<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Resources\AnnouncementResource;
use App\Http\Resources\HalaqaResource;
use App\Http\Resources\StudentResource;
use App\Models\Announcement;
use App\Models\Halaqa;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeacherHalaqaController extends Controller
{
    private function getTeacher(Request $request): Teacher
    {
        return Teacher::where('account_id', $request->user()->id)
            ->firstOrFail();
    }

    // GET /api/teacher/halaqat
    public function index(Request $request): JsonResponse
    {
        $teacher = $this->getTeacher($request);

        $halaqat = Halaqa::where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->withCount('students')
            ->with('students')
            ->get();

        return $this->apiSuccess(
            HalaqaResource::collection($halaqat)
        );
    }

    // GET /api/teacher/halaqat/{halaqa}/students
    public function students(Request $request, Halaqa $halaqa): JsonResponse
    {
        $teacher = $this->getTeacher($request);

        if ($halaqa->teacher_id !== $teacher->id) {
            return response()->json([
                'message' => 'هذه الحلقة ليست من حلقاتك',
            ], 403);
        }

        $students = $halaqa->students()
            ->orderBy('full_name')
            ->get();

        return $this->apiSuccess(
            StudentResource::collection($students)
        );
    }

    // GET /api/teacher/next-seance
    public function nextSeance(Request $request): JsonResponse
    {
        $teacher = $this->getTeacher($request);

        $nextSeance = $teacher->getNextSeance();

        if (! $nextSeance) {
            return $this->apiSuccess([
                'seance' => null,
            ], 'لا توجد جلسات قادمة');
        }

        return $this->apiSuccess([
            'seance' => [
                'id' => $nextSeance->id,
                'date' => $nextSeance->dateEntry?->date_value?->format('Y-m-d'),
                'halaqa' => [
                    'id' => $nextSeance->halaqa->id,
                    'name' => $nextSeance->halaqa->name,
                    'schedule' => $nextSeance->halaqa->schedule,
                ],
                'classroom' => $nextSeance->classroom ? [
                    'id' => $nextSeance->classroom->id,
                    'name' => $nextSeance->classroom->name,
                    'building' => $nextSeance->classroom->building,
                ] : null,
                'students_count' => $nextSeance->halaqa
                    ->students()
                    ->count(),
                'created_at' => $nextSeance->created_at?->toISOString(),
            ],
        ]);
    }

    // GET /api/teacher/announcements
    public function announcements(): JsonResponse
    {
        $announcements = Announcement::query()
            ->where(function ($q) {
                $q->whereJsonContains('target_roles', 'teacher')
                    ->orWhereJsonContains('target_roles', 'all');
            })
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>=', today());
            })
            ->with('admin')
            ->latest()
            ->get();

        return $this->apiSuccess(
            AnnouncementResource::collection($announcements)
        );
    }
}
