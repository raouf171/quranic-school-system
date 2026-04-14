<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'full_name'       => $this->full_name,
            'birth_date'      => $this->birth_date?->format('Y-m-d'),
            'enrollment_date' => $this->enrollment_date?->format('Y-m-d'),
            'social_state'    => $this->social_state,
            'fee_status'      => $this->fee_status,

           
            'halaqa' => $this->whenLoaded('halaqa', fn() => [
                'id'   => $this->halaqa->id,
                'name' => $this->halaqa->name,
            ]),

            'parent' => $this->whenLoaded('parent', fn() => [
                'id'   => $this->parent->id,
                'name' => $this->parent->name,
            ]),
        ];
    }
}