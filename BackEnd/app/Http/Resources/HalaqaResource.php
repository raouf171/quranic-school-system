<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HalaqaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'level'        => $this->level,
            'schedule'     => $this->schedule,
            'max_students' => $this->max_students,
            'is_active'    => $this->is_active,

            // whenCounted fonctionne avec withCount('students')
            'students_count' => $this->whenCounted('students'),

            'teacher' => $this->whenLoaded('teacher', fn() => [
                'id'   => $this->teacher->id,
                'name' => $this->teacher->name,
            ]),

            'students' => $this->whenLoaded('students', fn() =>
                $this->students->map(fn($s) => [
                    'id'        => $s->id,
                    'full_name' => $s->full_name,
                    'fee_status'=> $s->fee_status,
                ])
            ),
        ];
    }
}