<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
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

            'halaqa' => $this->whenLoaded('halaqa', fn() => [
                'id'     => $this->halaqa->id,
                'name'   => $this->halaqa->name,
                'gender' => $this->halaqa->gender,
            ]),

            'parent' => $this->whenLoaded('parent', fn() => [
                'id'    => $this->parent->id,
                'name'  => $this->parent->name,
                'phone' => $this->parent->phone,
            ]),
        ];
    }
}