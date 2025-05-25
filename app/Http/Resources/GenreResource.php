<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GenreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = [
            'id' => $this->id,
            'genre' => $this->genre,
        ];

        // Only include timestamps for single genre requests
        if ($request->route()->getName() === 'genres.show') {
            $data['created_at'] = $this->created_at;
            $data['updated_at'] = $this->updated_at;
        }

        return $data;
    }
}
