<?php

use App\Uom\Infrastructure\In\Http\UomController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/uom')->group(function () {
    // Term routes
    Route::prefix('measures')->controller(UomController::class)->group(function () {
        Route::post('/create', 'createMeasure');

        Route::get('/read', 'measureList');
        Route::get('/show/{id}', 'measureProfile');
        Route::post('/convert', 'convertUom');

        Route::put('/update/{id}', 'updateMeasure');
        Route::delete('/delete/{id}', 'deleteMeasure');



    });


    // Vocabulary routes

    Route::prefix('families')->controller(UomController::class)->group(function () {

    });
});