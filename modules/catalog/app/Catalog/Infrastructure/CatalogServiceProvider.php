<?php

namespace App\Catalog\Infrastructure;

use App\Catalog\Domain\Interfaces\ItemRepositoryInterface;
use App\Catalog\Infrastructure\Out\Models\EloquentItemRepository;
use App\Catalog\Infrastructure\Out\InMemory\InMemoryItemRepository;
use Illuminate\Support\ServiceProvider;

class CatalogServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Default binding (Eloquent/SQL)
        $this->app->bind(ItemRepositoryInterface::class, EloquentItemRepository::class);

        // === Adapter Configuration for Middleware ===
        $this->app->instance('adapters.catalog', [
            'interfaces' => [
                ItemRepositoryInterface::class => [
                    'local' => InMemoryItemRepository::class,
                    'eloquent' => EloquentItemRepository::class,
                ],
            ],
        ]);
    }

    public function boot(): void
    {
        // Register middleware alias
        $this->app['router']->aliasMiddleware('catalog_adapter', \App\Shared\Infrastructure\Middleware\AdapterMiddleware::class . ':catalog');

        // Load migrations from Catalog module
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');

        // Load routes from Catalog module
        $this->loadRoutesFrom(__DIR__ . '/In/Http/Routes/items_routes.php');
    }
}