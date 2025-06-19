<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceJsonResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Don't force JSON for Livewire requests (Filament admin panel)
        if (
            str_starts_with($request->path(), 'livewire') ||
            str_starts_with($request->path(), 'admin') ||
            str_starts_with($request->path(), 'filament')
        ) {
            return $next($request);
        }

        // Only force JSON for API requests
        if (str_starts_with($request->path(), 'api')) {
            $request->headers->set('Accept', 'application/json');
        }

        return $next($request);
    }
}
