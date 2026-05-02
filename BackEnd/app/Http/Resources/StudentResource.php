<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $halaqa = $this->relationLoaded('halaqa') ? $this->halaqa : null;
        $parent = $this->relationLoaded('parent') ? $this->parent : null;

        return [
            'id'                    => $this->id,
            'full_name'             => $this->full_name,
            'gender'                => $this->gender,
            'photo_url'             => $this->photo_url,
            'relationship_nature'   => $this->relationship_nature,
            'school_level'          => $this->school_level,
            'birth_date'            => $this->birth_date?->format('Y-m-d'),
            'enrollment_date'       => $this->enrollment_date?->format('Y-m-d'),
            'social_state'          => $this->social_state,
            'fee_status'            => $this->fee_status,

            'halaqa' => $halaqa ? [
                'id'     => $halaqa->id,
                'name'   => $halaqa->name,
                'gender' => $halaqa->gender,
                'schedule' => $halaqa->schedule,
                'next_seance' => $halaqa->next_seance_summary,
            ] : null,

            'parent' => $parent ? [
                'id'    => $parent->id,
                'name'  => $parent->name,
                'phone' => $parent->phone,
            ] : null,
        ];
    }
}