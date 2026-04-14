<?php

namespace App\Repositories;

use App\Models\Payment;
use App\Repositories\Interfaces\PaymentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class PaymentRepository implements PaymentRepositoryInterface
{
    public function getAll(?string $status = null): LengthAwarePaginator
    {
        $query = Payment::with(['student'])
                        ->latest('due_date');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->paginate(20);
    }

    public function findByStudent(int $studentId): Collection
    {
        return Payment::where('student_id', $studentId)
                      ->orderBy('month', 'desc')
                      ->get();
    }

    public function create(array $data): Payment
    {
        return Payment::create($data);
    }

    public function updateStatus(
        Payment $payment,
        string $status,
        ?string $paidDate = null
    ): Payment {
        $data = ['status' => $status];

        if ($status === 'paid' && $paidDate) {
            $data['paid_date'] = $paidDate;
        } elseif ($status === 'paid') {
            $data['paid_date'] = today()->format('Y-m-d');
        }

        $payment->update($data);
        return $payment->fresh();
    }
}