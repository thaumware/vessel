<?php

declare(strict_types=1);

namespace App\Admin\Infrastructure\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de autenticación para el panel de administración.
 * 
 * Usa autenticación básica con credenciales configurables via .env
 */
class AdminAuthMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si ya está autenticado en la sesión
        if ($request->session()->get('admin_authenticated')) {
            return $next($request);
        }

        // Verificar si es una solicitud de login
        if ($request->is('admin/login') || $request->is('admin/authenticate')) {
            return $next($request);
        }

        // Redirigir al login
        return redirect()->route('admin.login');
    }

    /**
     * Verificar credenciales de administrador.
     */
    public static function verifyCredentials(string $username, string $password): bool
    {
        $configUsername = config('admin.username', env('ADMIN_USERNAME', 'admin'));
        $configPassword = config('admin.password', env('ADMIN_PASSWORD'));

        // Si no hay password configurada, usar una por defecto en desarrollo
        if (empty($configPassword)) {
            // En producción, requerir password configurada
            if (app()->environment('production')) {
                return false;
            }
            // En desarrollo, usar password por defecto
            $configPassword = 'admin123';
        }

        // Verificar username
        if ($username !== $configUsername) {
            return false;
        }

        // Verificar password (soporta hash o texto plano)
        if (str_starts_with($configPassword, '$2y$')) {
            return Hash::check($password, $configPassword);
        }

        return $password === $configPassword;
    }
}
