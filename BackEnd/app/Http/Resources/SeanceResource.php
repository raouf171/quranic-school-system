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
            'occurrence_date' => $this->occurrence_date?->format('Y-m-d'),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'status' => $this->status,
            'cancel_reason' => $this->cancel_reason,
            'schedule_id' => $this->schedule_id,
            'is_extra' => $this->schedule_id === null,
            'notes' => $this->notes,

            'halaqa' => $this->whenLoaded('halaqa', fn() => [
                'id'   => $this->halaqa->id,
                'name' => $this->halaqa->name,
            ]),

            'schedule' => $this->whenLoaded('schedule', fn() => [
                'id' => $this->schedule->id,
                'weekday' => $this->schedule->weekday,
                'start_time' => $this->schedule->start_time,
                'end_time' => $this->schedule->end_time,
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