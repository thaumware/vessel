<?php

use App\Locations\Infrastructure\In\Http\Controllers\AddressController;
use Illuminate\Support\Facades\Route;

// Todas las rutas pasan por el middleware de adapter
Route::prefix('api/v1/addresses')->middleware('adapter')->group(function () {
    // READ - Listar todas las direcciones
    Route::get('/read', [AddressController::class, 'list']);
    
    // SHOW - Obtener una dirección específica
    Route::get('/show/{id}', [AddressController::class, 'show']);
    
    // CREATE - Crear una nueva dirección
    Route::post('/create', [AddressController::class, 'create']);
    
    // UPDATE - Actualizar una dirección existente
    Route::put('/update/{id}', [AddressController::class, 'update']);
    
    // DELETE - Eliminar una dirección
    Route::delete('/delete/{id}', [AddressController::class, 'delete']);
});
