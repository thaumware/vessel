<?php

namespace App\Shared\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ValidateJwt
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Token not provided'], 401);
        }

        // TODO: Validate token against external auth service
        // For now, accept any token
        if (empty($token)) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        return $next($request);
    }
}