<?php

namespace App\Repositories;

use App\Models\Student;
use App\Repositories\Interfaces\StudentRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB  ; 
use Illuminate\Database\Eloquent\Collection;



class StudentRepository implements StudentRepositoryInterface
{
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        // with() = eager loading pour éviter le problème N+1
        return Student::with(['halaqa', 'parent'])
                      ->latest()           // les plus récents 
                      ->paginate($perPage);
    }


    public function findById(int $id): ?Student
    {
        
        return Student::with(['halaqa', 'parent', 'payments'])
                      ->find($id);
    }

    public function findByHalaqa(int $halaqaId): Collection
    {
        return Student::where('halaqa_id', $halaqaId)
                      ->orderBy('full_name') 
                      ->get();
    }

    public function create(array $data): Student
    {
        
        return DB::transaction
        (function () use ($data) {
            return Student::create($data);
        });
    }

    public function update(Student $student, array $data): Student
    {
        $student->update($data);

        // fresh() recharge depuis la DB avec les nouvelles valeurs
      
        return $student->fresh(['halaqa', 'parent']);
    }

    public function delete(Student $student): bool
    {
        return $student->delete();
    }

    public function search(string $query): Collection
    {
        return Student::where('full_name', 'LIKE', "%{$query}%")
                      ->with('halaqa')
                
                      ->get();
    }
}