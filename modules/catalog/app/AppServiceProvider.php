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
        $this->registerModuleProviders();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Auto-register all module ServiceProviders
     * Convention: App\{Module}\Infrastructure\{Module}ServiceProvider
     */
    private function registerModuleProviders(): void
    {
        $appPath = app_path();
        $modules = array_filter(
            scandir($appPath),
            fn($dir) => $dir !== '.' && $dir !== '..' && $dir !== 'Shared' && is_dir($appPath . '/' . $dir)
        );

        foreach ($modules as $module) {
            $providerClass = "App\\{$module}\\Infrastructure\\{$module}ServiceProvider";
            
            if (class_exists($providerClass)) {
                $this->app->register($providerClass);
            }
        }
    }
}
