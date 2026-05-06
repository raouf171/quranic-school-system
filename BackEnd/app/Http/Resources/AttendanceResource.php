<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $student = $this->relationLoaded('student') ? $this->student : null;
        $seance = $this->relationLoaded('seance') ? $this->seance : null;

        return [
            'id'                => $this->id,
            'status'            => $this->status,
            'evaluation_grade'  => $this->evaluation_grade,
            'points' => $this->points,

            'student' => $student ? [
                'id'        => $student->id,
                'full_name' => $student->full_name,
            ] : null,

            'seance' => $seance ? [
                'id'   => $seance->id,
                'date' => $seance->dateEntry?->date_value?->format('Y-m-d'),
            ] : null,
        ];
    }
}