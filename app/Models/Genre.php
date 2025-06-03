<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Genre extends Model
{
    use HasUuids;

    protected $fillable = [
        'genre',
    ];
    public function artists()
    {
        return $this->belongsToMany(Artist::class, 'artist_genres')
            ->withTimestamps();
    }
}
