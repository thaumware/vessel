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

        // NO auto-install en boot - debe hacerse manualmente en setup o migrate
        // Solo registrar el runtime si ya está instalado
        try {
            /** @var object $adapter */
            $adapter = $this->app->make(self::PORTAL_ADAPTER);
            
            // Install Portal runtime with DB-backed adapter (no crea tablas, solo registra)
            $runtime = self::PORTAL_RUNTIME;
            $runtime::install($adapter);
        } catch (\Throwable $e) {
            // Si falla (DB no configurado), simplemente no instalar Portal
            // La app debe funcionar sin Portal
        }
    }
}
