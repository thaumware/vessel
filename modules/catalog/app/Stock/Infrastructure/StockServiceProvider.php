<?php

namespace App\Stock\Infrastructure;

use Illuminate\Support\ServiceProvider;
use App\Stock\Domain\Interfaces\StockItemRepositoryInterface;
use App\Stock\Domain\Interfaces\StockRepositoryInterface;
use App\Stock\Domain\Interfaces\MovementRepositoryInterface;
use App\Stock\Domain\Interfaces\BatchRepositoryInterface;
use App\Stock\Domain\Interfaces\UnitRepositoryInterface;
use App\Stock\Domain\Interfaces\CatalogGatewayInterface;
use App\Stock\Infrastructure\Out\Gateways\PortalCatalogGateway;

class StockServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // === Gateway Bindings (Infrastructure implementations) ===
        $this->app->singleton(
            CatalogGatewayInterface::class,
            PortalCatalogGateway::class
        );

        // === Repository Bindings (default: Eloquent) ===
        $this->app->bind(
            UnitRepositoryInterface::class,
            \App\Stock\Infrastructure\Out\Models\Eloquent\UnitRepository::class
        );

        $this->app->bind(
            BatchRepositoryInterface::class,
            \App\Stock\Infrastructure\Out\Models\Eloquent\BatchRepository::class
        );

        $this->app->bind(
            StockRepositoryInterface::class,
            \App\Stock\Infrastructure\Out\Models\Eloquent\StockRepository::class
        );

        $this->app->bind(
            MovementRepositoryInterface::class,
            \App\Stock\Infrastructure\Out\Models\Eloquent\MovementRepository::class
        );

        $this->app->bind(
            StockItemRepositoryInterface::class,
            \App\Stock\Infrastructure\Out\Models\Eloquent\StockItemRepository::class
        );

        // === Adapter Configuration for Middleware ===
        // El middleware AdapterMiddleware usa esta configuración para cambiar bindings según header
        $this->app->instance('adapters.stock', [
            'interfaces' => [
                StockItemRepositoryInterface::class => [
                    'local' => \App\Stock\Infrastructure\Out\InMemory\InMemoryStockItemRepository::class,
                    'eloquent' => \App\Stock\Infrastructure\Out\Models\Eloquent\StockItemRepository::class,
                ],
                // Agregar más interfaces cuando tengan implementación InMemory:
                // StockRepositoryInterface::class => [...],
                // MovementRepositoryInterface::class => [...],
            ],
        ]);
    }

    public function boot(): void
    {
        // Register middleware alias for module-specific adapter usage
        $this->app['router']->aliasMiddleware('stock_adapter', \App\Shared\Infrastructure\Middleware\AdapterMiddleware::class . ':stock');

        // Register migrations from Stock module
        $this->loadMigrationsFrom(__DIR__ . '/Out/Database/Migrations');

        // Register routes from Stock module
        $this->loadRoutesFrom(__DIR__ . '/In/Http/Routes/StockRoutes.php');
    }
}
