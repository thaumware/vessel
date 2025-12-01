<?php

namespace App\Uom\Infrastructure;

use App\Uom\Infrastructure\Out\Models\MeasureRepository;
use App\Uom\Domain\Interfaces\MeasureRepository as MeasureRepositoryInterface;
use App\Uom\Domain\Interfaces\ConversionRepository as ConversionRepositoryInterface;
use App\Uom\Infrastructure\Out\InMemory\InMemoryConversionRepository;
use App\Uom\Domain\Services\ConversionService;
use Illuminate\Support\ServiceProvider;

class UomServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind repositories
        $this->app->bind(
            MeasureRepositoryInterface::class,
            MeasureRepository::class
        );

        // Bind ConversionRepository (using InMemory with base data for now)
        $this->app->singleton(ConversionRepositoryInterface::class, function () {
            return new InMemoryConversionRepository(loadBaseData: true);
        });

        // Bind ConversionService
        $this->app->bind(ConversionService::class, function ($app) {
            return new ConversionService(
                $app->make(ConversionRepositoryInterface::class),
                $app->make(MeasureRepositoryInterface::class),
            );
        });
    }

    public function boot(): void
    {
        // Register middleware
        $this->app['router']->aliasMiddleware('adapter', \App\Shared\Infrastructure\Middleware\AdapterMiddleware::class . ':uom');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/Out/Database/Migrations');
        
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/In/Http/UomRoutes.php');

        // Register seeders command (for manual seeding)
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/Out/Database/Seeders' => database_path('seeders/Uom'),
            ], 'uom-seeders');
        }
    }
}
