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
    ->withMiddleware(function (Middleware $middleware): void {
        // CORS middleware para todas las peticiones
        $middleware->prepend(\Illuminate\Http\Middleware\HandleCors::class);
        
        $middleware->alias([
            'jwt.validate' => \App\Shared\Adapters\Http\Middleware\ValidateJwt::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Forzar JSON para todas las rutas API
        $exceptions->shouldRenderJsonWhen(function (Request $request, Throwable $e) {
            return $request->is('api/*') || $request->expectsJson();
        });
    })->create();
