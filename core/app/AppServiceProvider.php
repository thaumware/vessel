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
        $this->ensureSqliteDatabaseExists();

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

    private function ensureSqliteDatabaseExists(): void
    {
        if (config('database.default') !== 'sqlite') {
            return;
        }

        $dbPath = config('database.connections.sqlite.database');

        if (!$dbPath || $dbPath === ':memory:' || File::exists($dbPath)) {
            return;
        }

        try {
            File::ensureDirectoryExists(dirname($dbPath));
            File::put($dbPath, ''); // Best-effort creation for missing sqlite files
        } catch (\Throwable $e) {
            // Swallow to avoid blocking install flows; connection will still raise if inaccessible
        }
    }
}
