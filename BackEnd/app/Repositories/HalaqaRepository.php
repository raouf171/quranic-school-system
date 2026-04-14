<?php

namespace App\Repositories;

use App\Models\Halaqa;
use App\Repositories\Interfaces\HalaqaRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

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
        return Halaqa::create($data);
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