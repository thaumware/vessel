<?php

use App\Taxonomy\Infrastructure\In\Http\Controllers\TaxonomyController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1/taxonomy')->group(function () {
    // Term routes
    Route::prefix('terms')->group(function () {
        Route::post('/create', [TaxonomyController::class, 'createTerm']);

        Route::get('/read', [TaxonomyController::class, 'termList']);
        Route::get('/show/{id}', [TaxonomyController::class, 'termProfile']);

        Route::put('/update/{id}', [TaxonomyController::class, 'updateTerm']);
        Route::delete('/delete/{id}', [TaxonomyController::class, 'deleteTerm']);

        Route::prefix('relations')->group(function () {
            Route::post('/add', [TaxonomyController::class, 'addTermRelation']);
            Route::post('/remove', [TaxonomyController::class, 'removeTermRelation']);
        });

        Route::get('/tree', [TaxonomyController::class, 'getTermTree']);
        Route::get('/breadcrumb/{id}', [TaxonomyController::class, 'getTermBreadcrumb']);
    });


    // Vocabulary routes

    Route::prefix('vocabularies')->group(function () {
        Route::post('/create', [TaxonomyController::class, 'createVocabulary']);

        Route::get('/read', [TaxonomyController::class, 'vocabularyList']);
        Route::get('/show/{id}', [TaxonomyController::class, 'vocabularyProfile']);

        Route::put('/update/{id}', [TaxonomyController::class, 'updateVocabulary']);
        Route::delete('/delete/{id}', [TaxonomyController::class, 'deleteVocabulary']);


    });
});