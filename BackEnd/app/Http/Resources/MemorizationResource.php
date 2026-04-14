<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemorizationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'surah_start'       => $this->surah_start,
            'verse_start'       => $this->verse_start,
            'surah_end'         => $this->surah_end,
            'verse_end'         => $this->verse_end,
            'evaluation_grade'  => $this->evaluation_grade,
            'evaluation_points' => $this->evaluation_points,

            'student' => $this->whenLoaded('student', fn() => [
                'id'        => $this->student->id,
                'full_name' => $this->student->full_name,
            ]),

            // date vient de la séance liée
            'seance_date' => $this->whenLoaded('seance', fn() =>
                $this->seance->date?->format('Y-m-d')
            ),
        ];
    }
}