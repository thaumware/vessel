<?php

use App\Catalog\Infrastructure\In\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/items')->middleware('items_adapter')->group(function () {
    // READ - Listar todos los items
    Route::get('/read', [ItemController::class, 'list']);
    
    // SHOW - Obtener un item específico
    Route::get('/show/{id}', [ItemController::class, 'show']);
    
    // CREATE - Crear un nuevo item
    Route::post('/create', [ItemController::class, 'create']);
    
    // UPDATE - Actualizar un item existente
    Route::put('/update/{id}', [ItemController::class, 'update']);
    
    // DELETE - Eliminar un item
    Route::delete('/delete/{id}', [ItemController::class, 'delete']);
});
