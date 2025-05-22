<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

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
}
