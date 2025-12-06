<?php

namespace App\Shared\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Thaumware\Portal\Adapters\IlluminateAdapter;
use Thaumware\Portal\Portal;

class PortalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(IlluminateAdapter::class, function () {
            // Uses default DB connection / HTTP client / Schema bound to Laravel
            return new IlluminateAdapter(DB::connection(), Http::class ? Http::getFacadeRoot() : app('http'), Schema::getFacadeRoot());
        });
    }

    public function boot(): void
    {
        // Ensure shared + portal tables are registered for artisan migrate
        $this->loadMigrationsFrom(__DIR__ . '/../Infrastructure/Out/Database/Migrations');

        /** @var IlluminateAdapter $adapter */
        $adapter = $this->app->make(IlluminateAdapter::class);

        // Ensure portal tables exist before usage
        $adapter->install();

        // Install Portal runtime with DB-backed adapter
        Portal::install($adapter);
    }
}
