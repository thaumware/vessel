<?php

namespace App\Admin\Infrastructure;

use Illuminate\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind services if needed
    }

    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/In/Http/Routes/admin.php');
        
        // Load views
        $this->loadViewsFrom(__DIR__ . '/In/Views', 'admin');
    }
}
