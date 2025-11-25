<?php

use Illuminate\Support\Facades\Route;
use App\Stock\Infrastructure\In\Http\Controllers\StockController;
use App\Stock\Infrastructure\In\Http\Controllers\StockItemController;
use App\Stock\Infrastructure\In\Http\Controllers\UnitController;
use App\Stock\Infrastructure\In\Http\Controllers\BatchController;

Route::prefix('api/v1/stock')->middleware('adapter:stock')->group(function () {

    // === StockItems (existencia real vinculada al catálogo) ===
    Route::prefix('items')->group(function () {
        // CRUD básico
        Route::get('/list', [StockItemController::class, 'list']);
        Route::get('/show/{id}', [StockItemController::class, 'show']);
        Route::post('/create', [StockItemController::class, 'create']);
        Route::put('/update/{id}', [StockItemController::class, 'update']);
        Route::delete('/delete/{id}', [StockItemController::class, 'delete']);
        
        // Operaciones de inventario
        Route::post('/adjust', [StockItemController::class, 'adjust']);
        Route::post('/reserve/{id}', [StockItemController::class, 'reserve']);
        Route::post('/release/{id}', [StockItemController::class, 'release']);
    });

    // === Units (unidades de medida para stock) ===
    Route::prefix('units')->group(function () {
        Route::get('/list', [UnitController::class, 'list']);
        Route::get('/show/{id}', [UnitController::class, 'show']);
        Route::post('/create', [UnitController::class, 'create']);
    });

    // === Batches (lotes) ===
    Route::prefix('batches')->group(function () {
        Route::get('/list', [BatchController::class, 'list']);
        Route::get('/show/{id}', [BatchController::class, 'show']);
        Route::post('/create', [BatchController::class, 'create']);
    });

    // === Current stock (vista agregada por ubicación) ===
    Route::prefix('current')->group(function () {
        Route::get('/location/{locationId}', [StockController::class, 'index']);
    });

    // === Webhooks (movimientos externos) ===
    Route::prefix('webhooks')->group(function () {
        Route::post('/movement', [\App\Stock\Infrastructure\In\Http\Controllers\MovementWebhookController::class, 'receive']);
    });

});
