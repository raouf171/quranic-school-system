<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $students = $this->relationLoaded('students') ? $this->students : null;

        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'phone'      => $this->phone,
            'occupation' => $this->occupation,
            'address'    => $this->address,

            'students' => $students
                ? $students->map(fn($s) => [
                    'id'                    => $s->id,
                    'full_name'             => $s->full_name,
                    'gender'                => $s->gender,
                    'photo_url'             => $s->photo_url,
                    'relationship_nature'   => $s->relationship_nature,
                    'school_level'          => $s->school_level,
                    'fee_status'            => $s->fee_status,
                ])
                : null,
        ];
    }
}