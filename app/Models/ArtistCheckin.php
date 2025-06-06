<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ArtistCheckin extends Model
{
    use HasUuids;

    protected $fillable = ['artist_id', 'checkin_id'];

    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }

    public function checkin()
    {
        return $this->belongsTo(Checkin::class);
    }
}
