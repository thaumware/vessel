<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure;

use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/config/auth.php',
            'auth_admin'
        );
    }

    public function boot(): void
    {
        // Load routes with web middleware for session support
        $this->loadRoutesFrom(__DIR__ . '/In/Http/Routes/auth.php');
        
        // Load views
        $this->loadViewsFrom(__DIR__ . '/In/Views', 'auth');
        
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/Out/Database/Migrations');
    }
}
