<?php

namespace App\Items\Infrastructure;

use App\Items\Domain\Interfaces\ItemRepositoryInterface;
use App\Items\Infrastructure\Out\Models\EloquentItemRepository;
use App\Items\Infrastructure\Out\InMemory\InMemoryItemRepository;
use Illuminate\Support\ServiceProvider;

class ItemsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Default binding (Eloquent/SQL)
        $this->app->bind(ItemRepositoryInterface::class, EloquentItemRepository::class);

        // === Adapter Configuration for Middleware ===
        $this->app->instance('adapters.items', [
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
        $this->app['router']->aliasMiddleware('items_adapter', \App\Shared\Infrastructure\Middleware\AdapterMiddleware::class . ':items');

        // Load migrations from Items module
        $this->loadMigrationsFrom(__DIR__ . '/Migrations');

        // Load routes from Items module
        $this->loadRoutesFrom(__DIR__ . '/In/Http/Routes/items_routes.php');
    }
}