<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'student_id'  => $this->student_id,
            'month'       => $this->month,
            'amount'      => $this->amount,
            'due_date'    => $this->due_date?->format('Y-m-d'),
            'paid_date'   => $this->paid_date?->format('Y-m-d'),
            'status'      => $this->status,

            'student' => $this->whenLoaded('student', fn() => [
                'id'         => $this->student->id,
                'full_name'  => $this->student->full_name,
                'fee_status' => $this->student->fee_status,
            ]),
        ];
    }
}
