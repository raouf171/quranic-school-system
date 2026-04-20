<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RevisionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'surah'             => $this->surah,
            'verse_start'       => $this->verse_start,
            'verse_end'         => $this->verse_end,
            'evaluation_grade'  => $this->evaluation_grade,
            'evaluation_points' => $this->evaluation_points,

            'student' => $this->whenLoaded('student', fn() => [
                'id'        => $this->student->id,
                'full_name' => $this->student->full_name,
            ]),

            'seance_date' => $this->whenLoaded('seance', fn() =>
                $this->seance->date?->format('Y-m-d')
            ),
        ];
    }
}