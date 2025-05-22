<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Source extends Model
{
    use HasUuids;

    protected $fillable = [
        'source',
    ];

    protected $casts = [
        'source' => 'string',
    ];
}
