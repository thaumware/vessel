<?php

namespace App\Stock\Infrastructure\In\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Shared\Infrastructure\ConfigStore;

class StockTokenMiddleware
{
    public function handle(Request $request, Closure $next, string $requiredScope = 'public')
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['error' => 'Unauthorized', 'message' => 'Missing Bearer token'], 401);
        }

        /** @var ConfigStore $store */
        $store = app(ConfigStore::class);
        
        $publicToken = $store->get('stock.token.public') ?? env('STOCK_TOKEN_PUBLIC');
        $privateToken = $store->get('stock.token.private') ?? env('STOCK_TOKEN_PRIVATE');

        $scope = null;
        // Simple equality check. In production, consider hash comparison to avoid timing attacks, 
        // but for simple tokens this is acceptable as per requirement.
        if ($publicToken && $token === $publicToken) {
            $scope = 'public';
        } elseif ($privateToken && $token === $privateToken) {
            $scope = 'private';
        }

        if (!$scope) {
            return response()->json(['error' => 'Unauthorized', 'message' => 'Invalid token'], 401);
        }

        // Authorization logic:
        // 'private' scope satisfies 'public' requirement.
        // 'public' scope DOES NOT satisfy 'private' requirement.
        
        if ($requiredScope === 'private' && $scope !== 'private') {
             return response()->json(['error' => 'Forbidden', 'message' => 'Insufficient permissions (Private token required)'], 403);
        }

        // Add scope to request for controller usage if needed
        $request->attributes->set('token_scope', $scope);

        return $next($request);
    }
}
