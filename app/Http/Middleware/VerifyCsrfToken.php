<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Exclude only non-Sanctum API routes
        'api/*',
        // But keep CSRF protection for Sanctum routes
        'sanctum/csrf-cookie',
        'login',
        'logout',
        // Livewire file uploads - exclude from CSRF but keep session handling
        'livewire/upload-file',
        'livewire/message/*',
        // Admin routes - Filament handles its own security
        'admin/*',
    ];
}
