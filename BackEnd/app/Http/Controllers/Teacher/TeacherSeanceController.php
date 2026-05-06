<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Resources\SeanceResource;
use App\Models\DateEntry;
use App\Models\Halaqa;
use App\Models\Seance;
use App\Models\Teacher;
use App\Models\HalaqaSchedule;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeacherSeanceController extends Controller
{
    private function getTeacher(Request $request): Teacher
    {
        return Teacher::where('account_id', $request->user()->id)
                      ->firstOrFail();
    }

    // GET /api/teacher/seances/current-week
    public function currentWeek(Request $request): JsonResponse
    {
        $teacher = $this->getTeacher($request);
        [$weekStart, $weekEnd] = $this->resolveCurrentWeekWindow();

        $halaqat = Halaqa::where('teacher_id', $teacher->id)
            ->where('is_active', true)
            ->with([
                'schedules' => fn ($q) => $q->where('is_active', true)->orderBy('position'),
                'schedules.classroom',
            ])
            ->get();

        $rowsByKey = [];
        $halaqaIds = $halaqat->pluck('id')->all();

        // Build virtual pending rows from weekly recurring schedules.
        foreach ($halaqat as $halaqa) {
            foreach ($halaqa->schedules as $schedule) {
                $occurrenceDate = $this->projectOccurrenceDate($weekStart, (int) $schedule->weekday);
                $key = $this->occurrenceKey($halaqa->id, $schedule->id, $occurrenceDate->toDateString());

                $rowsByKey[$key] = [
                    'id' => null,
                    'schedule_id' => $schedule->id,
                    'halaqa_id' => $halaqa->id,
                    'halaqa_name' => $halaqa->name,
                    'occurrence_date' => $occurrenceDate->toDateString(),
                    'weekday' => (int) $occurrenceDate->dayOfWeek,
                    'start_time' => $schedule->start_time,
                    'end_time' => $schedule->end_time,
                    'classroom_id' => $schedule->classroom_id,
                    'classroom_name' => $schedule->classroom?->name,
                    'status' => 'pending',
                    'is_virtual' => true,
                    'is_extra' => false,
                    'cancel_reason' => null,
                ];
            }
        }

        if (!empty($halaqaIds)) {
            /** @var \Illuminate\Database\Eloquent\Collection<int, Seance> $seances */
            $seances = Seance::with(['halaqa', 'classroom', 'schedule'])
                ->whereIn('halaqa_id', $halaqaIds)
                ->whereDate('occurrence_date', '>=', $weekStart->toDateString())
                ->whereDate('occurrence_date', '<=', $weekEnd->toDateString())
                ->orderBy('occurrence_date')
                ->orderBy('start_time')
                ->get();

            foreach ($seances as $seance) {
                $occurrenceDate = optional($seance->occurrence_date)->toDateString();
                if (!$occurrenceDate) {
                    continue;
                }

                if ($seance->schedule_id) {
                    $key = $this->occurrenceKey($seance->halaqa_id, $seance->schedule_id, $occurrenceDate);
                } else {
                    $key = 'extra:' . $seance->id;
                }

                $rowsByKey[$key] = $this->formatWeeklyRow($seance);
            }
        }

        $rows = array_values($rowsByKey);
        usort($rows, function (array $a, array $b): int {
            return [$a['occurrence_date'], $a['start_time'] ?? '00:00:00', $a['halaqa_name']]
                <=> [$b['occurrence_date'], $b['start_time'] ?? '00:00:00', $b['halaqa_name']];
        });

        return $this->apiSuccess(
            $rows,
            null,
            200,
            [
                'week_start' => $weekStart->toDateString(),
                'week_end' => $weekEnd->toDateString(),
                'timezone' => config('app.timezone'),
            ]
        );
    }

    private function resolveCurrentWeekWindow(): array
    {
        $today = Carbon::now(config('app.timezone'))->startOfDay();
        $weekStartsOn = (int) config('seance.week_starts_on', 0);
        $diff = ($today->dayOfWeek - $weekStartsOn + 7) % 7;

        $weekStart = $today->copy()->subDays($diff);
        $weekEnd = $weekStart->copy()->addDays(6)->endOfDay();

        return [$weekStart, $weekEnd];
    }

    private function projectOccurrenceDate(Carbon $weekStart, int $targetWeekday): Carbon
    {
        $offset = ($targetWeekday - $weekStart->dayOfWeek + 7) % 7;
        return $weekStart->copy()->addDays($offset);
    }

    private function occurrenceKey(int $halaqaId, int $scheduleId, string $date): string
    {
        return implode(':', [$halaqaId, $scheduleId, $date]);
    }

    private function formatWeeklyRow(object $seance): array
    {
        $occurrenceDate = optional($seance->occurrence_date)->toDateString();

        return [
            'id' => $seance->id,
            'schedule_id' => $seance->schedule_id,
            'halaqa_id' => $seance->halaqa_id,
            'halaqa_name' => $seance->halaqa?->name,
            'occurrence_date' => $occurrenceDate,
            'weekday' => $seance->occurrence_date?->dayOfWeek,
            'start_time' => $seance->start_time ?? $seance->schedule?->start_time,
            'end_time' => $seance->end_time ?? $seance->schedule?->end_time,
            'classroom_id' => $seance->classroom_id,
            'classroom_name' => $seance->classroom?->name,
            'status' => $seance->status,
            'is_virtual' => false,
            'is_extra' => $seance->isExtra(),
            'cancel_reason' => $seance->cancel_reason,
        ];
    }

    // POST /api/teacher/schedules/{schedule}/open
    public function openSchedule(Request $request, HalaqaSchedule $schedule): JsonResponse
    {
        $request->validate([
            'occurrence_date' => 'required|date_format:Y-m-d',
        ]);

        $teacher = $this->getTeacher($request);
        $schedule->loadMissing('halaqa', 'classroom');

        if (! $schedule->halaqa || $schedule->halaqa->teacher_id !== $teacher->id) {
            return response()->json([
                'message' => 'ليس لديك صلاحية الوصول إلى هذا الموعد',
            ], 403);
        }

        $occurrenceDate = Carbon::createFromFormat(
            'Y-m-d',
            $request->string('occurrence_date'),
            config('app.timezone')
        )->startOfDay();

        $seance = DB::transaction(function () use ($schedule, $occurrenceDate, $teacher) {
            $existing = Seance::where('halaqa_id', $schedule->halaqa_id)
                ->where('schedule_id', $schedule->id)
                ->whereDate('occurrence_date', $occurrenceDate->toDateString())
                ->lockForUpdate()
                ->first();

            if ($existing) {
                if ($existing->status === 'cancelled') {
                    abort(response()->json([
                        'message' => 'لا يمكن فتح جلسة تم إلغاؤها',
                    ], 409));
                }

                return $existing;
            }

            $dateEntry = DateEntry::firstOrCreate(
                [
                    'date_value' => $occurrenceDate->toDateString(),
                    'created_by' => $teacher->id,
                ]
            );

            return Seance::create([
                'halaqa_id' => $schedule->halaqa_id,
                'created_by' => $teacher->id,
                'schedule_id' => $schedule->id,
                'occurrence_date' => $occurrenceDate->toDateString(),
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'classroom_id' => $schedule->classroom_id,
                'status' => 'scheduled',
                'date_id' => $dateEntry->id,
            ]);
        });

        $seance->load(['halaqa', 'classroom', 'schedule']);

        return $this->apiSuccess($this->formatWeeklyRow($seance));
    }

    // POST /api/teacher/schedules/{schedule}/cancel
    public function cancelSchedule(Request $request, HalaqaSchedule $schedule): JsonResponse
    {
        $request->validate([
            'occurrence_date' => 'required|date_format:Y-m-d',
            'reason' => 'required|string|max:500',
        ]);

        $teacher = $this->getTeacher($request);
        $schedule->loadMissing('halaqa', 'classroom');

        if (! $schedule->halaqa || $schedule->halaqa->teacher_id !== $teacher->id) {
            return response()->json([
                'message' => 'ليس لديك صلاحية الوصول إلى هذا الموعد',
            ], 403);
        }

        $occurrenceDate = Carbon::createFromFormat(
            'Y-m-d',
            $request->string('occurrence_date'),
            config('app.timezone')
        )->startOfDay();

        $seance = DB::transaction(function () use ($schedule, $occurrenceDate, $teacher, $request) {
            $existing = Seance::where('halaqa_id', $schedule->halaqa_id)
                ->where('schedule_id', $schedule->id)
                ->whereDate('occurrence_date', $occurrenceDate->toDateString())
                ->lockForUpdate()
                ->first();

            if ($existing) {
                if ($existing->status === 'held') {
                    abort(response()->json([
                        'message' => 'لا يمكن إلغاء جلسة تم تنفيذها',
                    ], 409));
                }

                $existing->update([
                    'status' => 'cancelled',
                    'cancel_reason' => $request->string('reason')->toString(),
                ]);

                return $existing;
            }

            $dateEntry = DateEntry::firstOrCreate(
                [
                    'date_value' => $occurrenceDate->toDateString(),
                    'created_by' => $teacher->id,
                ]
            );

            return Seance::create([
                'halaqa_id' => $schedule->halaqa_id,
                'created_by' => $teacher->id,
                'schedule_id' => $schedule->id,
                'occurrence_date' => $occurrenceDate->toDateString(),
                'start_time' => $schedule->start_time,
                'end_time' => $schedule->end_time,
                'classroom_id' => $schedule->classroom_id,
                'status' => 'cancelled',
                'cancel_reason' => $request->string('reason')->toString(),
                'date_id' => $dateEntry->id,
            ]);
        });

        $seance->load(['halaqa', 'classroom', 'schedule']);

        return $this->apiSuccess($this->formatWeeklyRow($seance));
    }

    // POST /api/teacher/seances/extra
    public function storeExtra(Request $request): JsonResponse
    {
        $request->validate([
            'halaqa_id' => 'required|integer|exists:halaqat,id',
            'occurrence_date' => 'required|date_format:Y-m-d',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'classroom_id' => 'nullable|integer|exists:classrooms,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $teacher = $this->getTeacher($request);
        $halaqa = Halaqa::findOrFail((int) $request->integer('halaqa_id'));

        if ($halaqa->teacher_id !== $teacher->id) {
            return response()->json([
                'message' => 'هذه الحلقة ليست من حلقاتك',
            ], 403);
        }

        $occurrenceDate = Carbon::createFromFormat(
            'Y-m-d',
            $request->string('occurrence_date'),
            config('app.timezone')
        )->startOfDay();
        $startTime = $request->string('start_time')->toString() . ':00';
        $endTime = $request->string('end_time')->toString() . ':00';

        if ($request->filled('classroom_id')) {
            $hasConflict = Seance::whereDate('occurrence_date', $occurrenceDate->toDateString())
                ->where('classroom_id', $request->integer('classroom_id'))
                ->where('status', '!=', 'cancelled')
                ->where(function ($q) use ($startTime, $endTime) {
                    $q->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                })
                ->exists();

            if ($hasConflict) {
                return response()->json([
                    'message' => 'هذه القاعة محجوزة بالفعل في نفس التوقيت',
                ], 422);
            }
        }

        $dateEntry = DateEntry::firstOrCreate([
            'date_value' => $occurrenceDate->toDateString(),
            'created_by' => $teacher->id,
        ]);

        $seance = Seance::create([
            'halaqa_id' => $halaqa->id,
            'created_by' => $teacher->id,
            'schedule_id' => null,
            'occurrence_date' => $occurrenceDate->toDateString(),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'classroom_id' => $request->integer('classroom_id') ?: null,
            'status' => 'held',
            'notes' => $request->string('notes')->toString() ?: null,
            'date_id' => $dateEntry->id,
        ]);

        $seance->load(['halaqa', 'classroom', 'schedule']);

        return $this->apiSuccess($this->formatWeeklyRow($seance), null, 201);
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
            ->orderBy('occurrence_date', 'desc')
            ->orderBy('start_time', 'desc')
            ->with(['classroom', 'schedule'])
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

        $seance->load(['halaqa', 'classroom', 'schedule', 'teacher']);

        return $this->apiSuccess(new SeanceResource($seance));
    }
}