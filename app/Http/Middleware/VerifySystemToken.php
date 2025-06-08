<?php

namespace App\Http\Middleware;

use App\Models\System;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifySystemToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->header('X-System-Token');

        if (!$token) {
            return response()->json([
                'message' => 'System token is required'
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!System::verifyToken('api_token', $token)) {
            return response()->json([
                'message' => 'Invalid system token'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
