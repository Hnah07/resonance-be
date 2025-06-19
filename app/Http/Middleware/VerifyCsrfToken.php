<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Support\Facades\Log;

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

    /**
     * Determine if the request has a valid CSRF token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function tokensMatch($request)
    {
        // Log CSRF token information for debugging
        if (app()->environment('production')) {
            Log::info('CSRF Token Check', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'has_token' => $request->has('_token'),
                'has_header' => $request->hasHeader('X-CSRF-TOKEN'),
                'has_cookie' => $request->hasCookie('XSRF-TOKEN'),
                'session_id' => $request->session()->getId(),
            ]);
        }

        return parent::tokensMatch($request);
    }
}
