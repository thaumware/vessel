<?php

namespace App\Stock\Infrastructure;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Broadcast;
use App\Stock\Domain\Interfaces\StockItemRepositoryInterface;
use App\Stock\Domain\Interfaces\StockRepositoryInterface;
use App\Stock\Domain\Interfaces\MovementRepositoryInterface;
use App\Stock\Domain\Interfaces\BatchRepositoryInterface;
use App\Stock\Domain\Interfaces\UnitRepositoryInterface;
use App\Stock\Domain\Interfaces\CatalogGatewayInterface;
use App\Stock\Domain\Interfaces\LocationStockSettingsRepositoryInterface;
use App\Stock\Domain\Interfaces\LocationGatewayInterface;
use App\Stock\Domain\Interfaces\LotRepositoryInterface;
use App\Stock\Domain\Services\StockCapacityService;
use App\Stock\Infrastructure\Out\Gateways\PortalCatalogGateway;
use App\Stock\Infrastructure\Out\Gateways\LocationsModuleGateway;
use App\Stock\Infrastructure\Services\MovementHandlerRegistry;
use App\Stock\Infrastructure\Handlers\CustomerLoanHandler;
use App\Stock\Infrastructure\Handlers\ConsignmentHandler;
use App\Shared\Domain\Interfaces\IdGeneratorInterface;
use App\Shared\Infrastructure\ModuleRegistry;
use App\Shared\Infrastructure\Services\UuidGenerator;

class StockServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        /** @var ModuleRegistry $modules */
        $modules = $this->app->make(ModuleRegistry::class);
        if (!$modules->enabled('stock')) {
            return;
        }
        // === Shared Bindings ===
        $this->app->bind(IdGeneratorInterface::class, UuidGenerator::class);
        // === Gateway Bindings (Infrastructure implementations) ===
        $this->app->singleton(
            CatalogGatewayInterface::class,
            PortalCatalogGateway::class
        );

        // Location Gateway: Stock -> Locations module adapter
        $this->app->singleton(
            LocationGatewayInterface::class,
            LocationsModuleGateway::class
        );

        // === Repository Bindings (default: Eloquent) ===
        $this->app->bind(
            UnitRepositoryInterface::class,
            \App\Stock\Infrastructure\Out\Models\Eloquent\UnitRepository::class
        );

        $this->app->bind(
            BatchRepositoryInterface::class,
            \App\Stock\Infrastructure\Out\Models\Eloquent\BatchRepository::class
        );

        $this->app->bind(
            StockRepositoryInterface::class,
            \App\Stock\Infrastructure\Out\Models\Eloquent\StockRepository::class
        );

        // Movement Repository: ahora Eloquent
        $this->app->bind(
            MovementRepositoryInterface::class,
            \App\Stock\Infrastructure\Out\Models\Eloquent\MovementRepository::class
        );

        $this->app->bind(
            StockItemRepositoryInterface::class,
            \App\Stock\Infrastructure\Out\Models\Eloquent\StockItemRepository::class
        );

        // Location Stock Settings Repository
        $this->app->bind(
            LocationStockSettingsRepositoryInterface::class,
            \App\Stock\Infrastructure\Out\Models\Eloquent\LocationStockSettingsRepository::class
        );

        // Lot Repository (para gestión de lotes con trazabilidad)
        $this->app->bind(
            LotRepositoryInterface::class,
            \App\Stock\Infrastructure\Out\InMemory\InMemoryLotRepository::class
        );

        // Reservation Repository (tracking ligero de reservas)
        $this->app->bind(
            \App\Stock\Domain\ReservationRepository::class,
            \App\Stock\Infrastructure\Persistence\MySQLReservationRepository::class
        );

        // === Domain Services ===
        $this->app->singleton(StockCapacityService::class, function ($app) {
            return new StockCapacityService(
                $app->make(LocationStockSettingsRepositoryInterface::class),
                $app->make(LocationGatewayInterface::class),
                $app->make(StockRepositoryInterface::class)
            );
        });

        // === Movement Handler Registry (Extensibilidad) ===
        $this->app->singleton(MovementHandlerRegistry::class, function ($app) {
            $registry = new MovementHandlerRegistry();
            
            // Registrar handlers custom EJEMPLO
            // Descomenta para activar:
            // $registry->register(new CustomerLoanHandler());
            // $registry->register(new ConsignmentHandler());
            
            // Los usuarios pueden agregar sus propios handlers aquí
            // O registrarlos desde su propio ServiceProvider
            
            return $registry;
        });

        // === Adapter Configuration for Middleware ===
        // El middleware AdapterMiddleware usa esta configuración para cambiar bindings según header
        $this->app->instance('adapters.stock', [
            'interfaces' => [
                StockItemRepositoryInterface::class => [
                    'local' => \App\Stock\Infrastructure\Out\InMemory\InMemoryStockItemRepository::class,
                    'eloquent' => \App\Stock\Infrastructure\Out\Models\Eloquent\StockItemRepository::class,
                ],
                MovementRepositoryInterface::class => [
                    'local' => \App\Stock\Infrastructure\Out\InMemory\InMemoryMovementRepository::class,
                    'eloquent' => \App\Stock\Infrastructure\Out\Models\Eloquent\MovementRepository::class,
                ],
                // Agregar más interfaces cuando tengan implementación InMemory:
                // StockRepositoryInterface::class => [...],
                // MovementRepositoryInterface::class => [...],
            ],
        ]);
    }

    public function boot(): void
    {
        /** @var ModuleRegistry $modules */
        $modules = $this->app->make(ModuleRegistry::class);
        if (!$modules->enabled('stock')) {
            return;
        }
        // Register middleware alias for module-specific adapter usage
        $this->app['router']->aliasMiddleware('stock_adapter', \App\Shared\Infrastructure\Middleware\AdapterMiddleware::class . ':stock');
        $this->app['router']->aliasMiddleware('stock.token', \App\Stock\Infrastructure\In\Http\Middleware\StockTokenMiddleware::class);

        // Register migrations from Stock module
        $this->loadMigrationsFrom(__DIR__ . '/Out/Database/Migrations');

        // Register routes from Stock module
        $this->loadRoutesFrom(__DIR__ . '/In/Http/Routes/StockRoutes.php');

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Stock\Application\Commands\ExpireReservationsCommand::class,
            ]);
        }

        if ($modules->wsEnabled('stock')) {
            Broadcast::routes(['middleware' => ['api']]);
            $this->loadRoutesFrom(__DIR__ . '/In/Http/Routes/StockChannels.php');
        }
    }
}
