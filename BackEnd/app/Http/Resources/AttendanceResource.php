<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'status'            => $this->status,
            'evaluation_grade'  => $this->evaluation_grade,
            'evaluation_points' => $this->evaluation_points,

            'student' => $this->whenLoaded('student', fn() => [
                'id'        => $this->student->id,
                'full_name' => $this->student->full_name,
            ]),

            'seance' => $this->whenLoaded('seance', fn() => [
                'id'   => $this->seance->id,
                'date' => $this->seance->date?->format('Y-m-d'),
            ]),
        ];
    }
}