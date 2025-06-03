<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class ArtistConcert extends Model
{
    use HasUuids;

    protected $table = 'artist_concerts';

    protected $fillable = [
        'artist_id',
        'concert_id',
    ];
}
