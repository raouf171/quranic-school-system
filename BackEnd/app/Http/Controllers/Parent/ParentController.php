<?php

namespace App\Http\Controllers\Parent;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendanceResource;
use App\Http\Resources\MemorizationResource;
use App\Http\Resources\RevisionResource;
use App\Http\Resources\StudentResource;
use App\Models\Announcement;
use App\Models\ParentProfile;
use App\Models\Payment;
use App\Models\Ranking;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ParentController extends Controller
{
    private function getParent(Request $request): ParentProfile
    {
        return ParentProfile::where('account_id', $request->user()->id)
                            ->firstOrFail();
    }

    private function authorizeChild(ParentProfile $parent, Student $student): void
    {
        if ($student->parent_id !== $parent->id) {
            abort(403, 'هذا الطالب ليس من أبنائك');
        }
    }

    // GET /api/parent/children
    public function children(Request $request): JsonResponse
    {
        $parent = $this->getParent($request);

        $students = Student::where('parent_id', $parent->id)
                           ->with('halaqa')
                           ->get();

        return response()->json(StudentResource::collection($students));
    }

    // GET /api/parent/children/{student}/attendance
    public function attendance(Request $request, Student $student): JsonResponse
    {
        $parent = $this->getParent($request);
        $this->authorizeChild($parent, $student);

        $attendances = $student->attendances()
                               ->with('seance')
                               ->latest()
                               ->paginate(20);

        return response()->json(AttendanceResource::collection($attendances));
    }

    // GET /api/parent/children/{student}/memorizations
    public function memorizations(Request $request, Student $student): JsonResponse
    {
        $parent = $this->getParent($request);
        $this->authorizeChild($parent, $student);

        $memorizations = $student->memorizations()
                                 ->with('seance')
                                 ->latest()
                                 ->paginate(20);

        return response()->json(MemorizationResource::collection($memorizations));
    }

    // GET /api/parent/children/{student}/revisions
    public function revisions(Request $request, Student $student): JsonResponse
    {
        $parent = $this->getParent($request);
        $this->authorizeChild($parent, $student);

        $revisions = $student->revisions()
                             ->with('seance')
                             ->latest()
                             ->paginate(20);

        return response()->json(RevisionResource::collection($revisions));
    }

    // GET /api/parent/children/{student}/payments
    public function payments(Request $request, Student $student): JsonResponse
    {
        $parent = $this->getParent($request);
        $this->authorizeChild($parent, $student);

        $payments = Payment::where('student_id', $student->id)
                           ->orderBy('month', 'desc')
                           ->get();

        return response()->json($payments->map(fn($p) => [
            'id'         => $p->id,
            'month'      => $p->month,
            'amount'     => $p->amount,
            'status'     => $p->status,
            'due_date'   => $p->due_date?->format('Y-m-d'),
            'paid_date'  => $p->paid_date?->format('Y-m-d'),
        ]));
    }

    // GET /api/parent/children/{student}/ranking
    public function ranking(Request $request, Student $student): JsonResponse
    {
        $parent = $this->getParent($request);
        $this->authorizeChild($parent, $student);

        $ranking = Ranking::where('student_id', $student->id)
                          ->where('halaqa_id', $student->halaqa_id)
                          ->latest('calculated_at')
                          ->first();

        if (!$ranking) {
            return response()->json([
                'message' => 'لا يوجد تصنيف بعد',
                'ranking' => null,
            ]);
        }

        return response()->json([
            'rank'         => $ranking->rank,
            'score'        => $ranking->score,
            'period_type'  => $ranking->period_type,
            'period_start' => $ranking->period_start?->format('Y-m-d'),
            'period_end'   => $ranking->period_end?->format('Y-m-d'),
            'calculated_at'=> $ranking->calculated_at?->format('Y-m-d H:i'),
        ]);
    }

    // GET /api/parent/announcements
    public function announcements(Request $request): JsonResponse
    {
        $announcements = Announcement::where(function ($q) {
                            $q->whereJsonContains('target_roles', 'parent')
                              ->orWhereJsonContains('target_roles', 'all');
                         })
                         ->where(function ($q) {
                            $q->whereNull('expiry_date')
                              ->orWhere('expiry_date', '>=', today());
                         })
                         ->latest()
                         ->get();

        return response()->json($announcements->map(fn($a) => [
            'id'         => $a->id,
            'title'      => $a->title,
            'content'    => $a->content,
            'created_at' => $a->created_at->format('Y-m-d H:i'),
            'expiry_date'=> $a->expiry_date?->format('Y-m-d'),
        ]));
    }
}