<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckinRating extends Model
{
    use HasUuids;

    protected $fillable = [
        'checkin_id',
        'rating',
    ];

    public function checkin(): BelongsTo
    {
        return $this->belongsTo(Checkin::class);
    }
}
