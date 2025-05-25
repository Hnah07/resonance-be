<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArtistResource extends JsonResource
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
            'description' => $this->description,
            'country' => $this->country?->name,
            'formed_year' => $this->formed_year,
            'image_url' => $this->image_url,
            'source' => $this->source?->source,
            'status' => $this->status?->status,
            'genres' => $this->genres->map(fn($genre) => [
                'id' => $genre->id,
                'name' => $genre->genre,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    /**
     * Get additional data that should be returned with the resource array.
     *
     * @return array<string, mixed>
     */
    public function with(Request $request): array
    {
        return [];
    }
}
