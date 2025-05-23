<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Location extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'street',
        'house_number',
        'postal_code',
        'city',
        'latitude',
        'longitude',
        'website',
        'country_id',
        'description',
        'source_id',
        'status_id',
    ];

    /**
     * Get the source that owns the location.
     */
    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    /**
     * Get the status that owns the location.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * Get the country that owns the location.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }
}
