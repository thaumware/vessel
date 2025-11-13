<?php

use App\Locations\Infrastructure\In\Http\Controllers\LocationsController;
use Illuminate\Support\Facades\Route;

// Todas las rutas pasan por el middleware de adapter
Route::prefix('api/v1/locations')->middleware('adapter')->group(function () {
    Route::post('/create', [LocationsController::class, 'create']);
    Route::get('/read', [LocationsController::class, 'list']);
    Route::get('/show/{id}', [LocationsController::class, 'show']);
    Route::put('/update/{id}', [LocationsController::class, 'update']);
    Route::delete('/delete/{id}', [LocationsController::class, 'delete']);
});