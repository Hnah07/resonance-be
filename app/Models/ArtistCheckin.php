<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ArtistCheckin extends Pivot
{
    use HasUuids;

    protected $fillable = ['artist_id', 'checkin_id'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function artist()
    {
        return $this->belongsTo(Artist::class);
    }

    public function checkin()
    {
        return $this->belongsTo(Checkin::class);
    }
}
