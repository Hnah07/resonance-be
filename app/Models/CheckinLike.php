<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class CheckinLike extends Pivot
{
    use HasUuids;

    protected $table = 'checkin_likes';

    protected $fillable = [
        'checkin_id',
        'user_id'
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) \Illuminate\Support\Str::uuid();
            }
        });
    }

    public function checkin()
    {
        return $this->belongsTo(Checkin::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
