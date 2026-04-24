<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemorizationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $student = $this->relationLoaded('student') ? $this->student : null;
        $seance = $this->relationLoaded('seance') ? $this->seance : null;

        return [
            'id'                => $this->id,
            'surah_start'       => $this->surah_start,
            'verse_start'       => $this->verse_start,
            'surah_end'         => $this->surah_end,
            'verse_end'         => $this->verse_end,
            'evaluation_grade'  => $this->evaluation_grade,
            'points' => $this->points,

            'student' => $student ? [
                'id'        => $student->id,
                'full_name' => $student->full_name,
            ] : null,

            'seance_date' => $seance
                ? $seance->dateEntry?->date_value?->format('Y-m-d')
                : null,
        ];
    }
}