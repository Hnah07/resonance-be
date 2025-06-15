<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class CheckinResource extends JsonResource
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
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'username' => $this->user->username,
                'profile_photo_url' => $this->user->profile_photo_url,
            ],
            'concert' => [
                'id' => $this->concert->id,
                'date' => $this->concert->date,
                'event' => [
                    'id' => $this->concert->event->id,
                    'name' => $this->concert->event->name,
                    'type' => $this->concert->event->type,
                    'image_url' => $this->concert->event->image_url,
                ],
                'location' => [
                    'id' => $this->concert->location->id,
                    'name' => $this->concert->location->name,
                    'city' => $this->concert->location->city,
                    'country' => $this->concert->location->country->name,
                ],
                'artists' => $this->artists->map(fn($artist) => [
                    'id' => $artist->id,
                    'name' => $artist->name,
                    'image_url' => $artist->image_url,
                    'genres' => $artist->genres->map(fn($genre) => [
                        'id' => $genre->id,
                        'name' => $genre->genre,
                    ]),
                ]),
            ],
            'photos' => $this->photos->map(fn($photo) => [
                'id' => $photo->id,
                'url' => $photo->url,
                'caption' => $photo->caption,
            ]),
            'likes_count' => $this->likes->count(),
            'comments_count' => $this->comments->count(),
            'comments' => $this->comments->map(fn($comment) => [
                'id' => $comment->id,
                'comment' => $comment->comment,
                'created_at' => $comment->created_at,
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'username' => $comment->user->username,
                    'profile_photo_url' => $comment->user->profile_photo_url,
                ],
            ]),
            'is_liked' => $this->likedByUsers->contains('id', Auth::id()),
            'rating' => $this->rating?->rating,
            'review' => $this->review?->review,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
