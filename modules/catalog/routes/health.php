<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
        'env' => config('app.env'),
    ], 200);
});

// Minimal root route (no Laravel branding) to avoid 404/landing page issues
Route::get('/', function () {
    return response()->json([
        'status' => 'ready',
        'app' => config('app.name'),
        'env' => config('app.env'),
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
