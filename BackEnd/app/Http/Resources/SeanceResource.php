<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SeanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'    => $this->id,
'date' => $this->date_value,
            'notes' => $this->notes,

            'halaqa' => $this->whenLoaded('halaqa', fn() => [
                'id'   => $this->halaqa->id,
                'name' => $this->halaqa->name,
            ]),

            'classroom' => $this->whenLoaded('classroom', fn() => [
                'id'       => $this->classroom->id,
                'name'     => $this->classroom->name,
                'building' => $this->classroom->building,
            ]),

            'teacher' => $this->whenLoaded('teacher', fn() => [
                'id'   => $this->teacher->id,
                'name' => $this->teacher->name,
            ]),
        ];
    }
}