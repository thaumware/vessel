<?php

use Illuminate\Support\Facades\Route;
use App\Stock\Infrastructure\In\Http\Controllers\StockController;
use App\Stock\Infrastructure\In\Http\Controllers\UnitController;
use App\Stock\Infrastructure\In\Http\Controllers\BatchController;

Route::prefix('api/v1/stock')->group(function () {

	// Units
	Route::prefix('units')->group(function () {
		Route::post('/create', [UnitController::class, 'create']);
		Route::get('/read', [UnitController::class, 'list']);
		Route::get('/show/{id}', [UnitController::class, 'show']);
	});

	// Batches
	Route::prefix('batches')->group(function () {
		Route::post('/create', [BatchController::class, 'create']);
		Route::get('/read', [BatchController::class, 'list']);
		Route::get('/show/{id}', [BatchController::class, 'show']);
	});

	// Current stock by location
	Route::prefix('current')->group(function () {
		Route::get('/location/{locationId}', [StockController::class, 'index']);
	});

	// Webhooks for external movement notifications
	Route::prefix('webhooks')->group(function () {
		Route::post('/movement', [\App\Stock\Infrastructure\In\Http\Controllers\MovementWebhookController::class, 'receive']);
	});

});
