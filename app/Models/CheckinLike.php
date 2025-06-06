<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckinLike extends Model
{
    use HasUuids;

    protected $fillable = [
        'checkin_id',
        'user_id'
    ];

    public function checkin(): BelongsTo
    {
        return $this->belongsTo(Checkin::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
