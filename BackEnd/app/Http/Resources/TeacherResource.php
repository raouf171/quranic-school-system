<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeacherResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'hiring_date'  => $this->hiring_date?->format('Y-m-d'),
            'is_available' => $this->is_available,

            'halaqat' => $this->whenLoaded('halaqat', fn() =>
                $this->halaqat->map(fn($h) => [
                    'id'   => $h->id,
                    'name' => $h->name,
                ])
            ),

            // withCount('halaqat') ajoute halaqat_count au modèle
            'halaqat_count' => $this->whenCounted('halaqat'),
        ];
    }
}