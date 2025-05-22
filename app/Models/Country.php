<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Country extends Model
{
    use HasUuids;

    protected $fillable = [
        'code',
        'name',
        'official_name',
        'native_name',
        'continent',
        'subregion',
        'emoji',
        'latitude',
        'longitude',
    ];
}
