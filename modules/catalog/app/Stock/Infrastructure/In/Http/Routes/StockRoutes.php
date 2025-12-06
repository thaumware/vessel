<?php

use Illuminate\Support\Facades\Route;
use App\Stock\Infrastructure\In\Http\Controllers\StockController;
use App\Stock\Infrastructure\In\Http\Controllers\StockItemController;
use App\Stock\Infrastructure\In\Http\Controllers\UnitController;
use App\Stock\Infrastructure\In\Http\Controllers\BatchController;
use App\Stock\Infrastructure\In\Http\Controllers\LotController;
use App\Stock\Infrastructure\In\Http\MovementController;
use App\Stock\Infrastructure\In\Http\CapacityController;

Route::prefix('api/v1/stock')->middleware('adapter:stock')->group(function () {

    // === StockItems (existencia real vinculada al catálogo) ===
    Route::prefix('items')->group(function () {
        // CRUD
        Route::get('/read', [StockItemController::class, 'list']);
        Route::get('/show/{id}', [StockItemController::class, 'show']);
        Route::post('/create', [StockItemController::class, 'create']);
        Route::put('/update/{id}', [StockItemController::class, 'update']);
        Route::delete('/delete/{id}', [StockItemController::class, 'delete']);
        
        // Operaciones de inventario
        Route::post('/adjust', [StockItemController::class, 'adjust']);
        Route::post('/reserve/{id}', [StockItemController::class, 'reserve']);
        Route::post('/release/{id}', [StockItemController::class, 'release']);
    });

    // === Movements (movimientos de stock) ===
    Route::prefix('movements')->group(function () {
        Route::get('/', [MovementController::class, 'index']);
        Route::get('/types', [MovementController::class, 'types']);
        Route::post('/validate', [MovementController::class, 'validate']);
        Route::get('/{id}', [MovementController::class, 'show']);
        Route::post('/', [MovementController::class, 'store']);
        
        // Operaciones específicas (helpers)
        Route::post('/receipt', [MovementController::class, 'receipt']);
        Route::post('/shipment', [MovementController::class, 'shipment']);
        Route::post('/reserve', [MovementController::class, 'reserve']);
        Route::post('/release', [MovementController::class, 'release']);
        Route::post('/adjustment', [MovementController::class, 'adjustment']);
        Route::post('/transfer', [MovementController::class, 'transfer']);
    });

    // === Capacity (configuración de capacidad por ubicación) ===
    Route::prefix('capacity')->group(function () {
        Route::get('/{locationId}', [CapacityController::class, 'show']);
        Route::post('/', [CapacityController::class, 'store']);
        Route::delete('/{locationId}', [CapacityController::class, 'destroy']);
        
        // Consultas
        Route::get('/{locationId}/can-accept', [CapacityController::class, 'canAccept']);
        Route::get('/{locationId}/stats', [CapacityController::class, 'stats']);
        Route::get('/{locationId}/available', [CapacityController::class, 'available']);
        Route::get('/{locationId}/total-stock', [CapacityController::class, 'totalStock']);
        Route::get('/{locationId}/unique-item-ids', [CapacityController::class, 'uniqueItemIds']);
        Route::get('/{locationId}/is-full', [CapacityController::class, 'isFull']);
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

    // === Lots (lotes con trazabilidad) ===
    Route::prefix('lots')->group(function () {
        // CRUD
        Route::get('/read', [LotController::class, 'list']);
        Route::get('/show/{id}', [LotController::class, 'show']);
        Route::post('/create', [LotController::class, 'create']);
        Route::put('/update/{id}', [LotController::class, 'update']);
        Route::delete('/delete/{id}', [LotController::class, 'delete']);
        
        // Búsqueda por número de lote
        Route::get('/by-number/{lotNumber}', [LotController::class, 'findByLotNumber']);
        
        // Cambios de estado
        Route::post('/quarantine/{id}', [LotController::class, 'quarantine']);
        Route::post('/activate/{id}', [LotController::class, 'activate']);
        Route::post('/deplete/{id}', [LotController::class, 'deplete']);
        
        // Alertas de vencimiento
        Route::get('/expiring-soon', [LotController::class, 'expiringSoon']);
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
