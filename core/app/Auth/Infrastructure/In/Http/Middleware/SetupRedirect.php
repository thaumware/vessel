<?php

namespace App\Auth\Infrastructure\In\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetupRedirect
{
    public function handle(Request $request, Closure $next)
    {
        // Si está instalado, bloquear acceso al setup
        if ($this->isInstalled()) {
            // Si intentan acceder a /setup, redirigir al admin
            if ($request->is('setup') || $request->is('setup/*')) {
                return redirect('/admin')->with('error', 'La aplicación ya está instalada. Elimina APP_INSTALLED del .env para reinstalar.');
            }
            return $next($request);
        }

        // Si NO está instalado, redirigir todo al setup excepto setup mismo
        if ($request->is('setup') || $request->is('setup/*')) {
            return $next($request);
        }

        return redirect('/setup');
    }

    private function isInstalled(): bool
    {
        // Solo verificar .env, no intentar acceder a BD
        $envPath = base_path('.env');
        
        // Si no existe .env, no está instalado
        if (!file_exists($envPath)) {
            return false;
        }

        // Si existe APP_INSTALLED=true en .env, está instalado
        $envFlag = env('APP_INSTALLED');
        return $envFlag !== null && filter_var($envFlag, FILTER_VALIDATE_BOOLEAN);
    }
}
