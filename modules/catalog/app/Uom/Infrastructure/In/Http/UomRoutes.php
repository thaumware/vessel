<?php

use App\Uom\Infrastructure\In\Http\UomController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/uom')->group(function () {
    // Measures CRUD
    Route::prefix('measures')->controller(UomController::class)->group(function () {
        Route::get('/read', 'measureList');
        Route::get('/show/{id}', 'measureProfile');
        Route::post('/create', 'createMeasure');
        Route::put('/update/{id}', 'updateMeasure');
        Route::delete('/delete/{id}', 'deleteMeasure');
        
        // Actions
        Route::post('/convert', 'convertUom');
    });


    // Vocabulary routes

    Route::prefix('families')->controller(UomController::class)->group(function () {

    });
});