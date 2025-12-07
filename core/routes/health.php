<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
        'env' => config('app.env'),
        'version' => config('app.version'),
        'timestamp' => now()->toIso8601String(),
    ], 200);
});

Route::get('/api/status', function () {
    return response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
        'env' => config('app.env'),
        'version' => config('app.version'),
        'timestamp' => now()->toIso8601String(),
    ], 200);
});

Route::get('/', function () {
    return view('landing', [
        'appName' => config('app.name'),
        'env' => config('app.env'),
        'version' => config('app.version'),
        'timestamp' => now()->toDayDateTimeString(),
        'status' => 'ready',
    ]);
});

Route::get('/health/db', function () {
    try {
        DB::connection()->getPdo();
        return response()->json(['status' => 'ok', 'db' => true], 200);
    } catch (\Throwable $e) {
        return response()->json(['status' => 'error', 'db' => false, 'error' => $e->getMessage()], 503);
    }
});
