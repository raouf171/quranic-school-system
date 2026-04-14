<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'content'      => $this->content,
            'target_roles' => $this->target_roles, // array auto grâce au cast
            'expiry_date'  => $this->expiry_date?->format('Y-m-d'),
            'created_at'   => $this->created_at->format('Y-m-d H:i'),

            'created_by' => $this->whenLoaded('admin', fn() => [
                'name' => $this->admin->name,
            ]),
        ];
    }
}