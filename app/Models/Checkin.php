<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Checkin extends Model
{
    use HasUuids;

    protected $fillable = [
        'concert_id',
        'user_id',
    ];

    protected $casts = [];

    public function concert(): BelongsTo
    {
        return $this->belongsTo(Concert::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(CheckinPhoto::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(CheckinLike::class);
    }

    public function likedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'checkin_likes')
            ->withTimestamps()
            ->using(CheckinLike::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(CheckinComment::class);
    }

    public function artistCheckins(): HasMany
    {
        return $this->hasMany(ArtistCheckin::class);
    }

    public function artists(): BelongsToMany
    {
        return $this->belongsToMany(Artist::class, 'artist_checkins')
            ->withTimestamps()
            ->using(ArtistCheckin::class);
    }

    public function rating()
    {
        return $this->hasOne(CheckinRating::class);
    }

    public function review()
    {
        return $this->hasOne(CheckinReview::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::saved(function ($checkin) {
            if (request()->has('artists')) {
                // First detach all existing artists
                $checkin->artists()->detach();

                // Then attach the new artists one by one
                foreach (request()->input('artists') as $artistId) {
                    $checkin->artists()->attach($artistId);
                }
            }
        });
    }
}
