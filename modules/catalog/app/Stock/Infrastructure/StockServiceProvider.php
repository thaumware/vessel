<?php

namespace App\Stock\Infrastructure;

use Illuminate\Support\ServiceProvider;

class StockServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind repositories
        $this->app->bind(
            \App\Stock\Domain\Interfaces\UnitRepositoryInterface::class,
            \App\Stock\Infrastructure\Out\Models\Eloquent\UnitRepository::class
        );

        $this->app->bind(
            \App\Stock\Domain\Interfaces\BatchRepositoryInterface::class,
            \App\Stock\Infrastructure\Out\Models\Eloquent\BatchRepository::class
        );

        $this->app->bind(
            \App\Stock\Domain\Interfaces\StockRepositoryInterface::class,
            \App\Stock\Infrastructure\Out\Models\Eloquent\StockRepository::class
        );

        $this->app->bind(
            \App\Stock\Domain\Interfaces\MovementRepositoryInterface::class,
            \App\Stock\Infrastructure\Out\Models\Eloquent\MovementRepository::class
        );
    }

    public function boot(): void
    {
        // Register middleware alias for module-specific adapter usage
        $this->app['router']->aliasMiddleware('stock_adapter', \App\Shared\Infrastructure\Middleware\AdapterMiddleware::class . ':stock');

        // No event listener registered here: adapters call UseCases directly.

        // Register migrations from Stock module (relative to this file)
        $this->loadMigrationsFrom(__DIR__ . '/Out/Database/Migrations');

        // Register routes from Stock module
        $this->loadRoutesFrom(__DIR__ . '/In/Http/Routes/StockRoutes.php');
    }
}
