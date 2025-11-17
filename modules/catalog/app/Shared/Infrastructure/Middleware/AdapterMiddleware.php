<?php

namespace App\Shared\Infrastructure\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdapterMiddleware
{
    private array $moduleConfigs = [
        // Cada módulo debe definir su propia config en su AdapterMiddleware específico
        // No cargar configs aquí para mantener separación de responsabilidades
    ];

    public function handle(Request $request, Closure $next, string $module)
    {
        $adapter = $request->header('X-' . strtoupper($module) . '-ADAPTER', 'eloquent');

        if (!isset($this->moduleConfigs[$module])) {
            return $next($request);
        }

        $config = $this->moduleConfigs[$module];

        if ($adapter === 'local' && isset($config['inmemory'])) {
            if (isset($config['interfaces'])) {
                // Multiple interfaces (like Taxonomy)
                foreach ($config['interfaces'] as $interface) {
                    if (isset($config['inmemory'][$interface])) {
                        app()->bind($interface, $config['inmemory'][$interface]);
                    }
                }
            } else {
                // Single interface (like Locations)
                app()->bind($config['interface'], $config['inmemory']);
            }
        } else {
            if (isset($config['interfaces'])) {
                // Multiple interfaces (like Taxonomy)
                foreach ($config['interfaces'] as $interface) {
                    if (isset($config['eloquent'][$interface])) {
                        app()->bind($interface, $config['eloquent'][$interface]);
                    }
                }
            } else {
                // Single interface (like Locations)
                app()->bind($config['interface'], $config['eloquent']);
            }
        }

        return $next($request);
    }
}