<?php

use App\Admin\Infrastructure\In\Http\Controllers\AdminPanelController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    // Dashboard principal
    Route::get('/', [AdminPanelController::class, 'index'])->name('dashboard');
    
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
