<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

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
}
