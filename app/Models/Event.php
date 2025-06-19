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

        // If it starts with 'storage/', remove it to avoid double paths
        if (str_starts_with($value, 'storage/')) {
            $value = substr($value, 8); // Remove 'storage/' prefix
        }

        // For both local and production, use asset helper
        return asset('storage/' . $value);
    }

    /**
     * Set the image URL.
     */
    public function setImageUrlAttribute($value)
    {
        if (!$value) {
            $this->attributes['image_url'] = null;
            return;
        }

        // If it's a full URL, extract just the path
        if (str_starts_with($value, 'http')) {
            // Extract the path from the URL
            $path = parse_url($value, PHP_URL_PATH);
            if (str_starts_with($path, '/storage/')) {
                $path = substr($path, 9); // Remove '/storage/' prefix
            }
            $this->attributes['image_url'] = $path;
            return;
        }

        // If it starts with 'storage/', remove it
        if (str_starts_with($value, 'storage/')) {
            $value = substr($value, 8);
        }

        $this->attributes['image_url'] = $value;
    }
}
