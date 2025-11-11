<?php

namespace App\Uom\Infrastructure;

use App\Uom\Infrastructure\Out\Models\MeasureRepository;
use App\Uom\Domain\Interfaces\MeasureRepository as MeasureRepositoryInterface;
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
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/Out/Database/Migrations');
        $this->loadRoutesFrom(__DIR__ . '/In/Http/UomRoutes.php');
    }
}
