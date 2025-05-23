<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConcertResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'event' => [
                'id' => $this->event->id,
                'name' => $this->event->name,
                'type' => $this->event->type,
            ],
            'location' => [
                'id' => $this->location->id,
                'name' => $this->location->name,
                'city' => $this->location->city,
                'country' => $this->location->country->name,
            ],
            'date' => $this->date,
            'source' => $this->source->source,
            'status' => $this->status->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
