<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Checkin extends Model
{
    use HasUuids;

    protected $fillable = ['concert_id', 'user_id'];

    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }

    public function user()
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
            ->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(CheckinComment::class);
    }
}
