<?php

declare(strict_types=1);

namespace App\Shared\Providers;

use Illuminate\Support\ServiceProvider;

class HealthServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadRoutesFrom(base_path('routes/health.php'));
    }
}
