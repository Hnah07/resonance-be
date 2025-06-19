<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

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

    public function concerts(): HasMany
    {
        return $this->hasMany(Concert::class);
    }

    /**
     * Get the full image URL.
     */
    public function getImageUrlAttribute($value)
    {
        if (!$value) {
            return null;
        }

        // If it's already a full URL, return it
        if (str_starts_with($value, 'http')) {
            return $value;
        }

        // For both local and production, use asset helper
        return asset('storage/' . $value);
    }
}
