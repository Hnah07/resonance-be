<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Status extends Model
{
    use HasUuids;

    protected $fillable = [
        'status',
    ];

    // cast status to string for when it's retrieved from the database
    protected $casts = [
        'status' => 'string',
    ];
}
