<?php

use App\Admin\Infrastructure\In\Http\Controllers\AdminPanelController;
use App\Admin\Infrastructure\Middleware\AdminAuthMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    // Rutas publicas (sin autenticacion)
    Route::get('/login', [AdminPanelController::class, 'showLogin'])->name('login');
    Route::post('/authenticate', [AdminPanelController::class, 'authenticate'])->name('authenticate');

    // Rutas protegidas (requieren autenticacion)
    Route::middleware(AdminAuthMiddleware::class)->group(function () {
        // Dashboard principal
        Route::get('/', [AdminPanelController::class, 'index'])->name('dashboard');
        Route::post('/logout', [AdminPanelController::class, 'logout'])->name('logout');
        
        // Tests
        Route::post('/tests/run', [AdminPanelController::class, 'runTests'])->name('tests.run');
        
        // Database
        Route::get('/database', [AdminPanelController::class, 'database'])->name('database');
        Route::get('/database/table/{table}', [AdminPanelController::class, 'tableData'])->name('database.table');
        
        // Migrations
        Route::post('/migrate', [AdminPanelController::class, 'migrate'])->name('migrate');
        Route::get('/migrations/status', [AdminPanelController::class, 'migrationsStatus'])->name('migrations.status');
        
        // Seeders
        Route::post('/seed', [AdminPanelController::class, 'seed'])->name('seed');
        
        // Cache
        Route::post('/cache/clear', [AdminPanelController::class, 'clearCache'])->name('cache.clear');
    });
});
