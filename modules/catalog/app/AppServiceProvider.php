<?php

namespace App;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Module providers are registered explicitly in bootstrap/providers.php
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
