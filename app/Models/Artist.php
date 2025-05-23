<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Artist extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'description',
        'country_id',
        'formed_year',
        'source_id',
        'status_id',
        'image_url',
    ];

    /**
     * Get the source that owns the artist.
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    /**
     * Get the status that owns the artist.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * Get the country that owns the artist.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
