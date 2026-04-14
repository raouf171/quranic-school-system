<?php

namespace App\Repositories\Interfaces;

use App\Models\Halaqa;
use Illuminate\Database\Eloquent\Collection;

interface HalaqaRepositoryInterface
{
    public function getAll(): Collection;

    public function findById(int $id): ?Halaqa;

    public function findByTeacher(int $teacherId): Collection;

    public function create(array $data): Halaqa;

    public function update(Halaqa $halaqa, array $data): Halaqa;

    public function deactivate(Halaqa $halaqa): bool;
}