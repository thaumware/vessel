<?php

namespace App\Admin\Infrastructure;

use Illuminate\Support\ServiceProvider;

class AdminServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            base_path('config/admin.php'),
            'admin'
        );
    }

    public function boot(): void
    {
        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/In/Http/Routes/admin.php');
        
        // Load views
        $this->loadViewsFrom(__DIR__ . '/In/Views', 'admin');
        
        // Publish config
        $this->publishes([
            base_path('config/admin.php') => config_path('admin.php'),
        ], 'admin-config');
    }
}
