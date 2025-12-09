<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Shared\Console\Commands\AutoUpdateCommand;

return Application::configure(basePath: dirname(__DIR__))
    ->withCommands([
        AutoUpdateCommand::class,
    ])
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: __DIR__ . '/../app/Shared/Infrastructure/In/Http/Routes/HealthRoutes.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // CORS middleware para todas las peticiones
        $middleware->prepend(\Illuminate\Http\Middleware\HandleCors::class);
        
        $middleware->alias([
            'jwt.validate' => \App\Shared\Adapters\Http\Middleware\ValidateJwt::class,
            'vessel.access' => \App\Auth\Infrastructure\In\Http\Middleware\VesselAccessMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Forzar JSON para todas las rutas API
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });
    })->create();
