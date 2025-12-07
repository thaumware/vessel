<?php

namespace App\Shared\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
// Portal package is optional; use strings to avoid static-analysis errors when not installed

class PortalServiceProvider extends ServiceProvider
{
    private const PORTAL_ADAPTER = 'Thaumware\\Portal\\Adapters\\IlluminateAdapter';
    private const PORTAL_RUNTIME = 'Thaumware\\Portal\\Portal';

    public function register(): void
    {
        // Si el paquete Portal no está instalado, salimos sin registrar nada
        if (!class_exists(self::PORTAL_ADAPTER)) {
            return;
        }

        $this->app->singleton(self::PORTAL_ADAPTER, function () {
            $httpClient = Http::getFacadeRoot() ?? app(\Illuminate\Http\Client\Factory::class);
            $schema = Schema::getFacadeRoot() ?? app('db.schema');

            // Usa la conexión por defecto
            $adapterClass = self::PORTAL_ADAPTER;
            return new $adapterClass(DB::connection(), $httpClient, $schema);
        });
    }

    public function boot(): void
    {
        // Si el paquete Portal no está instalado, no hacemos nada
        if (!class_exists(self::PORTAL_ADAPTER) || !class_exists(self::PORTAL_RUNTIME)) {
            return;
        }

        // Ensure shared + portal tables are registered for artisan migrate
        $this->loadMigrationsFrom(__DIR__ . '/../Infrastructure/Out/Database/Migrations');

        /** @var object $adapter */
        $adapter = $this->app->make(self::PORTAL_ADAPTER);

        // Ensure portal tables exist before usage
        if (method_exists($adapter, 'install')) {
            $adapter->install();
        }

        // Install Portal runtime with DB-backed adapter
        $runtime = self::PORTAL_RUNTIME;
        $runtime::install($adapter);
    }
}
