<?php

namespace App\Repositories;

use App\Models\Halaqa;
use App\Repositories\Interfaces\HalaqaRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class HalaqaRepository implements HalaqaRepositoryInterface
{
    public function getAll(): Collection
    {
        return Halaqa::with(['teacher', 'students'])
                     ->withCount('students') // عدد الطلبة
                     ->orderBy('name')
                     ->get();
    }

    public function findById(int $id): ?Halaqa
    {
        return Halaqa::with([
                        'teacher',
                        'students',
                        'seances' => fn($q) => $q->latest()->limit(5) 
                     ])
                     ->withCount('students')
                     ->find($id);
    }

    public function findByTeacher(int $teacherId): Collection
    {
        return Halaqa::where('teacher_id', $teacherId)
                     ->where('is_active', true) 
                     ->withCount('students')
                     ->get();
    }

    public function create(array $data): Halaqa
    {
        return DB::transaction(function () use ($data) {
            $scheduleSlots = $data['schedules'] ?? [];
            unset($data['schedules'], $data['schedule']);

            $halaqa = Halaqa::create($data);

            if (! empty($scheduleSlots)) {
                $slotsToInsert = array_map(
                    static function (array $slot, $index): array {
                        return [
                            'weekday' => (int) $slot['weekday'],
                            'start_time' => $slot['start_time'],
                            'end_time' => $slot['end_time'],
                            'classroom_id' => $slot['classroom_id'] ?? null,
                            'is_active' => $slot['is_active'] ?? true,
                            'position' => $slot['position'] ?? $index,
                        ];
                    },
                    $scheduleSlots,
                    array_keys($scheduleSlots)
                );

                $halaqa->schedules()->createMany($slotsToInsert);
            }

            return $halaqa->fresh(['teacher', 'students', 'schedules']);
        });
    }

    public function update(Halaqa $halaqa, array $data): Halaqa
    {
        $halaqa->update($data);
        return $halaqa->fresh(['teacher', 'students']);
    }

    public function deactivate(Halaqa $halaqa): bool
    {
        
        return $halaqa->update(['is_active' => false]);
    }
}