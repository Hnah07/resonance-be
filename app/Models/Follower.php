<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Follower extends Model

{
    use HasUuids;
    protected $fillable = ['follower_id', 'followed_id'];

    // Users that the current user follows
    public function followings()
    {
        return $this->hasMany(Follower::class, 'follower_id');
    }

    // Users that follow the current user
    public function followers()
    {
        return $this->hasMany(Follower::class, 'followed_id');
    }
}
