<?php

namespace App;

use Illuminate\Support\ServiceProvider;
use App\Shared\Infrastructure\ModuleRegistry;
use App\Shared\Infrastructure\ConfigStore;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Module providers are registered explicitly in bootstrap/providers.php
        $this->app->singleton(ModuleRegistry::class, function ($app) {
            return new ModuleRegistry(config('modules.modules', []), $app->make(ConfigStore::class));
        });

        $this->app->singleton(ConfigStore::class, fn() => new ConfigStore());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // NO auto-crear base de datos - eso se hace en setup
        // Solo configurar fallbacks si la app ya está configurada

        // Fallback: if session driver is database but table missing, use array to avoid 500s
        if (config('session.driver') === 'database') {
            try {
                if (!Schema::hasTable('sessions')) {
                    config(['session.driver' => 'array']);
                }
            } catch (\Throwable $e) {
                config(['session.driver' => 'array']);
            }
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Shared\Console\Commands\AutoUpdateCommand::class,
                \App\Shared\Console\Commands\CloneTestDatabaseCommand::class,
                \App\Shared\Console\Commands\ResetAdminCredentialsCommand::class,
            ]);
        }
    }
}
