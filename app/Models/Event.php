<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Event extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'description',
        'type',
        'image_url',
        'source_id',
        'status_id',
    ];

    /**
     * Get the source that owns the event.
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    /**
     * Get the status that owns the event.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }
}
