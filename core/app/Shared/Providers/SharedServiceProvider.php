<?php

declare(strict_types=1);

namespace App\Shared\Providers;

use Illuminate\Support\ServiceProvider;

class SharedServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Load shared routes (testing, utilities, etc.)
        $this->loadRoutesFrom(
            base_path('app/Shared/Infrastructure/In/Http/Routes/SharedRoutes.php')
        );
    }
}
