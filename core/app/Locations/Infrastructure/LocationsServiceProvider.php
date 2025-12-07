<?php

namespace App\Locations\Infrastructure;

use App\Locations\Domain\Interfaces\AddressRepository;
use App\Locations\Domain\Interfaces\LocationRepository;
use App\Locations\Infrastructure\In\Http\Middleware\AdapterMiddleware;
use App\Locations\Infrastructure\Out\Models\Eloquent\EloquentAddressRepository;
use App\Locations\Infrastructure\Out\Models\Eloquent\EloquentLocationRepository;
use Illuminate\Support\ServiceProvider;

class LocationsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Default binding (fallback)
        $this->app->bind(LocationRepository::class, EloquentLocationRepository::class);
        $this->app->bind(AddressRepository::class, EloquentAddressRepository::class);
    }

    public function boot(): void
    {
        // Register middleware
        $this->app['router']->aliasMiddleware('adapter', AdapterMiddleware::class . ':locations');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/Out/Database/Migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/In/Http/Routes/LocationsRoutes.php');
        $this->loadRoutesFrom(__DIR__ . '/In/Http/Routes/AddressRoutes.php');
    }
}