<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
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
            'name' => $this->name,
            'street' => $this->street,
            'house_number' => $this->house_number,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'country' => $this->country?->name,
            'country_code' => $this->country?->code,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'description' => $this->description,
            'image_url' => $this->image_url,
            'website' => $this->website,
            'source' => $this->source?->source,
            'status' => $this->status?->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
