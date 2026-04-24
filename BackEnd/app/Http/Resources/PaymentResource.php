<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $student = $this->relationLoaded('student') ? $this->student : null;

        return [
            'id'          => $this->id,
            'student_id'  => $this->student_id,
            'month'       => $this->month,
            'amount'      => $this->amount,
            'due_date'    => $this->due_date?->format('Y-m-d'),
            'paid_date'   => $this->paid_date?->format('Y-m-d'),
            'status'      => $this->status,

            'student' => $student ? [
                'id'         => $student->id,
                'full_name'  => $student->full_name,
                'fee_status' => $student->fee_status,
            ] : null,
        ];
    }
}
