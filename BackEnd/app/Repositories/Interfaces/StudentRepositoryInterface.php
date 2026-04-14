<?php

namespace App\Repositories\Interfaces;

use App\Models\Student;
use Illuminate\Pagination\LengthAwarePaginator;


interface StudentRepositoryInterface
{
  //les fonctinons L maain
    public function getAll(int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Student;

    public function findByHalaqa(int $halaqaId): \Illuminate\Database\Eloquent\Collection;

    public function create(array $data): Student;

    public function update(Student $student, array $data): Student;

    public function delete(Student $student): bool;




    // Chercher un étudiant par nom

    public function search(string $query): \Illuminate\Database\Eloquent\Collection;
}