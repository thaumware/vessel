<?php

namespace App\Stock\Infrastructure;

use Illuminate\Support\ServiceProvider;

class StockServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind repositories

    }

    public function boot(): void
    {

        // Register migrations from Stock module (relative to this file)
        $this->loadMigrationsFrom(__DIR__ . '/Out/Database/Migrations');

        // Register routes from Stock module
        $this->loadRoutesFrom(__DIR__ . '/In/Http/Routes/StockRoutes.php');
    }
}
