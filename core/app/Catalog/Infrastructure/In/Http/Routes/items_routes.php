<?php

use App\Catalog\Infrastructure\In\Http\Controllers\ItemController;
use App\Catalog\Infrastructure\In\Http\Controllers\ItemIdentifierController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/items')->group(function () {
    Route::get('/by-barcode/{barcode}', [ItemIdentifierController::class, 'findByBarcode']);
    Route::get('/by-identifier/{value}', [ItemIdentifierController::class, 'findByIdentifier']);

    // READ - Listar todos los items
    Route::get('/read', [ItemController::class, 'list']);
    
    // SHOW - Obtener un item específico
    Route::get('/show/{id}', [ItemController::class, 'show']);
    
    // CREATE - Crear un nuevo item
    Route::post('/create', [ItemController::class, 'create']);

    // IDENTIFIERS - Crear identificadores para un item
    Route::post('/{id}/identifiers/create', [ItemIdentifierController::class, 'create']);
    
    // UPDATE - Actualizar un item existente
    Route::put('/update/{id}', [ItemController::class, 'update']);
    
    // DELETE - Eliminar un item
    Route::delete('/delete/{id}', [ItemController::class, 'delete']);
});
