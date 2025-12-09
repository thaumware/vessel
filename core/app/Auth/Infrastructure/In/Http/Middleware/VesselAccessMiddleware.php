<?php

namespace App\Auth\Infrastructure\In\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VesselAccessMiddleware
{
    public function handle(Request $request, Closure $next, string $requiredScope = 'all')
    {
        // HOTFIX: Desactivar seguridad temporalmente
        return $next($request);

        // 1. Check Header
        $token = $request->header('VESSEL-ACCESS-PRIVATE');

        // if (!$token) {
        //     // Fallback to Bearer for backward compatibility or public endpoints if needed, 
        //     // but user specifically asked for VESSEL-ACCESS-PRIVATE for non-public.
        //     // Let's check if it's a public route? The middleware is applied to protected routes.
        //     return response()->json(['error' => 'Unauthorized', 'message' => 'Missing VESSEL-ACCESS-PRIVATE header'], 401);
        // }

        // // 2. Validate Token in DB
        // $record = DB::table('auth_access_tokens')->where('token', $token)->first();

        // if (!$record) {
        //     return response()->json(['error' => 'Unauthorized', 'message' => 'Invalid token'], 401);
        // }

        // // 3. Check Scope
        // // 'all' token can access everything. 'own' token might be restricted (logic to be defined, but we validate the string).
        // // If the route requires 'all' but token is 'own', maybe deny?
        // // User said: "scope (all, own nomas por ahora)"
        
        // // Logic: If token scope is 'all', it passes.
        // // If token scope is 'own', and required is 'all', maybe fail? 
        // // For now, let's assume 'all' > 'own'.
        
        // if ($requiredScope === 'all' && $record->scope !== 'all') {
        //      return response()->json(['error' => 'Forbidden', 'message' => 'Insufficient scope'], 403);
        // }

        // 4. Inject Context
        $request->attributes->set('workspace_id', $record->workspace_id);
        $request->attributes->set('token_scope', $record->scope);

        return $next($request);
    }
}
