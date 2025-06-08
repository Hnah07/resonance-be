<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Hash;

class System extends Model
{
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'key',
        'value',
        'description',
        'type',
        'is_public',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_public' => 'boolean',
        'value' => 'json',
    ];

    /**
     * Get a system setting by its key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function getSetting(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Set a system setting.
     *
     * @param string $key
     * @param mixed $value
     * @param string|null $description
     * @param string $type
     * @param bool $isPublic
     * @return System
     */
    public static function setSetting(string $key, $value, ?string $description = null, string $type = 'string', bool $isPublic = false): System
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'description' => $description,
                'type' => $type,
                'is_public' => $isPublic,
            ]
        );
    }

    /**
     * Set a system token (hashed).
     *
     * @param string $key
     * @param string $token
     * @param string|null $description
     * @return System
     */
    public static function setToken(string $key, string $token, ?string $description = null): System
    {
        return static::setSetting(
            $key,
            Hash::make($token),
            $description,
            'token',
            false
        );
    }

    /**
     * Verify if a token matches the stored hash.
     *
     * @param string $key
     * @param string $token
     * @return bool
     */
    public static function verifyToken(string $key, string $token): bool
    {
        $setting = static::where('key', $key)->first();

        if (!$setting || $setting->type !== 'token') {
            return false;
        }

        return Hash::check($token, $setting->value);
    }
}
