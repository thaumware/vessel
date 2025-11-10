<?php

namespace App\Shared\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Thaumware\Portal\Portal;
use Thaumware\Portal\Adapters\IlluminateAdapter;

class PortalServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $adapter = new IlluminateAdapter(
            DB::getFacadeRoot(),
            Http::getFacadeRoot(),
            Schema::getFacadeRoot()
        );

        // Install Portal in DI container
        Portal::install($adapter);

        // Create tables (non-blocking)
        try {
            $adapter->install();
        } catch (\Exception) {
            // Database not ready - run migrations later
        }
    }
}
