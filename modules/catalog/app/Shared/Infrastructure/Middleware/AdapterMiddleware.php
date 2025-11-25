<?php

namespace App\Shared\Infrastructure\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Middleware para cambiar el adaptador de repositorios según el header X-{MODULE}-ADAPTER.
 * 
 * Cada módulo debe registrar su configuración de adaptadores en el container:
 *   app()->instance('adapters.{module}', [
 *       'interfaces' => [Interface::class => ['local' => InMemory::class, 'eloquent' => Eloquent::class]],
 *   ]);
 */
class AdapterMiddleware
{
    public function handle(Request $request, Closure $next, string $module)
    {
        $adapter = $request->header('X-' . strtoupper($module) . '-ADAPTER', 'eloquent');
        
        // Obtener configuración de adaptadores del módulo
        $config = app()->bound("adapters.{$module}") 
            ? app("adapters.{$module}") 
            : null;

        if ($config && isset($config['interfaces'])) {
            foreach ($config['interfaces'] as $interface => $implementations) {
                $implementation = $implementations[$adapter] ?? $implementations['eloquent'] ?? null;
                
                if ($implementation && class_exists($implementation)) {
                    app()->bind($interface, $implementation);
                }
            }
        }

        // Guardar el adaptador actual para que otros servicios puedan consultarlo
        app()->instance("adapter.{$module}", $adapter);

        return $next($request);
    }
}