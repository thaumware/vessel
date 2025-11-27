<?php

use App\Locations\Infrastructure\In\Http\Controllers\LocationController;
use Illuminate\Support\Facades\Route;

// Todas las rutas pasan por el middleware de adapter
Route::prefix('api/v1/locations')->middleware('adapter')->group(function () {
    // READ - Listar todos los recursos
    Route::get('/read', [LocationController::class, 'list']);
    
    // SHOW - Obtener un recurso específico
    Route::get('/show/{id}', [LocationController::class, 'show']);
    
    // CREATE - Crear un nuevo recurso
    Route::post('/create', [LocationController::class, 'create']);
    
    // UPDATE - Actualizar un recurso existente
    Route::put('/update/{id}', [LocationController::class, 'update']);
    
    // DELETE - Eliminar un recurso
    Route::delete('/delete/{id}', [LocationController::class, 'delete']);
});