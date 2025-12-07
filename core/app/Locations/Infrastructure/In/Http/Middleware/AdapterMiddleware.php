<?php

namespace App\Locations\Infrastructure\In\Http\Middleware;

use App\Locations\Domain\Interfaces\LocationRepository;
use App\Locations\Infrastructure\Out\InMemory\InMemoryLocationRepository;
use App\Locations\Infrastructure\Out\Models\Eloquent\EloquentLocationRepository;
use Closure;
use Illuminate\Http\Request;

class AdapterMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $adapter = $request->header('X-LOCATION-ADAPTER');

        // Si el header es 'local', usar InMemory, si no usar Eloquent
        if ($adapter === 'local') {
            app()->bind(LocationRepository::class, InMemoryLocationRepository::class);
        } else {
            app()->bind(LocationRepository::class, EloquentLocationRepository::class);
        }

        return $next($request);
    }
}