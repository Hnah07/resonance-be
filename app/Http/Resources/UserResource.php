<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $isAuthenticated = Auth::check();
        $isCurrentUser = $isAuthenticated && Auth::id() === $this->id;
        $isFollowing = false;

        if ($isAuthenticated) {
            $isFollowing = $this->followers()->where('follower_id', Auth::id())->exists();
        }

        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'profile_photo_url' => $this->profile_photo_url,
            'bio' => $this->bio,
            'city' => $this->city,
            'country' => $this->country ? [
                'id' => $this->country->id,
                'name' => $this->country->name,
            ] : null,
            'country_name' => $this->country ? $this->country->name : null,
            'created_at' => $this->created_at,
            'stats' => [
                'followers_count' => $this->followers()->count(),
                'following_count' => $this->following()->count(),
                'checkins_count' => $this->checkins()->count(),
                'concerts_count' => $this->checkins()->distinct('concert_id')->count(),
                'artists_count' => $this->checkins()
                    ->join('artist_checkins', 'checkins.id', '=', 'artist_checkins.checkin_id')
                    ->distinct('artist_id')
                    ->count(),
            ],
            'is_following' => $isFollowing,
            'is_current_user' => $isCurrentUser,
        ];

        // Add authenticated-only data
        if ($isAuthenticated) {
            $data = array_merge($data, [
                'email' => $this->email,
                'role' => $this->role,
                'is_active' => $this->is_active,
            ]);
        }

        return $data;
    }
}
