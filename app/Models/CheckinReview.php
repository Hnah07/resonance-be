<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CheckinReview extends Model
{
    use HasUuids;

    protected $fillable = ['review', 'checkin_id'];

    public function checkin()
    {
        return $this->belongsTo(Checkin::class);
    }
}
