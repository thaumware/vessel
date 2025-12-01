<?php

use App\Auth\Infrastructure\In\Http\Controllers\AdminPanelController;
use Illuminate\Support\Facades\Route;

// Solo disponible en entorno local/desarrollo
if (app()->environment('local', 'development', 'testing')) {
    Route::prefix('admin')->name('admin.')->middleware('web')->group(function () {
        Route::get('/', [AdminPanelController::class, 'index'])->name('dashboard');
        Route::post('/tests/run', [AdminPanelController::class, 'runTests'])->name('tests.run');
        Route::get('/database', [AdminPanelController::class, 'database'])->name('database');
        Route::get('/database/table/{table}', [AdminPanelController::class, 'tableData'])->name('database.table');
        Route::post('/migrate', [AdminPanelController::class, 'migrate'])->name('migrate');
        Route::post('/seed', [AdminPanelController::class, 'seed'])->name('seed');
        Route::post('/cache/clear', [AdminPanelController::class, 'clearCache'])->name('cache.clear');
    });
}
