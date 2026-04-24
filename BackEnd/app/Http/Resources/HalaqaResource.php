<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HalaqaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $teacher = $this->relationLoaded('teacher') ? $this->teacher : null;
        $students = $this->relationLoaded('students') ? $this->students : null;

        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'gender'       => $this->gender,
            'level'        => $this->level,
            'schedule'     => $this->schedule,
            'next_seance'  => $this->next_seance_summary,
            'max_students' => $this->max_students,
            'is_active'    => $this->is_active,

            // whenCounted fonctionne avec withCount('students')
            'students_count' => $this->whenCounted('students'),

            'teacher' => $teacher ? [
                'id'   => $teacher->id,
                'name' => $teacher->name,
            ] : null,

            'students' => $students
                ? $students->map(fn($s) => [
                    'id'        => $s->id,
                    'full_name' => $s->full_name,
                    'fee_status'=> $s->fee_status,
                ])
                : null,
        ];
    }
}