<?php

namespace App\Items\Infrastructure;

use App\Items\Domain\Interfaces\ItemRepositoryInterface;
use App\Items\Infrastructure\Out\Models\EloquentItemRepository;
use Illuminate\Support\ServiceProvider;

class ItemsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Default binding (fallback)
        $this->app->bind(ItemRepositoryInterface::class, EloquentItemRepository::class);
    }

    public function boot(): void
    {
        // Register middleware
        $this->app['router']->aliasMiddleware('adapter', \App\Shared\Infrastructure\Middleware\AdapterMiddleware::class . ':items');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/Out/Database/Migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/In/Http/Routes/ItemsRoutes.php');
    }
}