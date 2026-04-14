<?php

namespace App\Repositories\Interfaces;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface PaymentRepositoryInterface
{
    public function getAll(?string $status = null): LengthAwarePaginator;

    public function findByStudent(int $studentId): Collection;

    public function create(array $data): Payment;

    public function updateStatus(Payment $payment, string $status, ?string $paidDate = null): Payment;
}