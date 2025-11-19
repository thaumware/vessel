<?php

namespace App\Pricing\Infrastructure;

use App\Pricing\Domain\Interfaces\PricingRepository;
use App\Pricing\Infrastructure\Out\Models\Eloquent\EloquentPricingRepository;
use Illuminate\Support\ServiceProvider;

class PricingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Default binding (fallback)
        // $this->app->bind(PricingRepository::class, EloquentPricingRepository::class);
    }

    public function boot(): void
    {
        // Register middleware
        // $this->app['router']->aliasMiddleware('adapter', \App\Shared\Infrastructure\Middleware\AdapterMiddleware::class . ':pricing');

        // // Load migrations
        // $this->loadMigrationsFrom(__DIR__ . '/Out/Database/Migrations');

        // // Load routes
        // $this->loadRoutesFrom(__DIR__ . '/In/Http/Routes/PricingRoutes.php');
    }
}