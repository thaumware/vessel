<?php

use App\Shared\Infrastructure\In\Http\Controllers\TestingController;
use Illuminate\Support\Facades\Route;

// Testing administration routes
Route::middleware(['web'])->prefix('admin')->group(function () {
    // Testing database configuration
    Route::get('/tests/config', [TestingController::class, 'showConfig']);
    Route::post('/tests/config', [TestingController::class, 'updateConfig']);
    Route::post('/tests/config/test', [TestingController::class, 'testConnection']);
    
    // Execute tests
    Route::post('/tests/run', [TestingController::class, 'runTests']);
    Route::get('/tests/files', [TestingController::class, 'listTestFiles']);
});
