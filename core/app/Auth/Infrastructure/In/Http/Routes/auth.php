<?php

use App\Auth\Infrastructure\In\Http\Controllers\AdminPanelController;
use App\Auth\Infrastructure\In\Http\Controllers\SetupController;
use App\Auth\Infrastructure\In\Http\Middleware\SetupRedirect;
use Illuminate\Support\Facades\Route;

// Setup wizard (no auth) for first install
Route::middleware('web')->group(function () {
    Route::get('/setup', [SetupController::class, 'show'])->name('setup.show');
    Route::post('/setup', [SetupController::class, 'store'])
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
        ->name('setup.store');
});

// Login form handler (no auth)
Route::middleware('web')->group(function () {
    Route::post('/admin/login', function (\Illuminate\Http\Request $request) {
        $credentials = $request->only('username', 'password');
        
        $adminRoot = env('ADMIN_ROOT', 'admin');
        $adminPassword = env('ADMIN_ROOT_PASSWORD');
        
        if (!$adminPassword) {
            return response()->json(['success' => false, 'error' => 'Admin no configurado. Ve a /setup'], 500);
        }
        
        if ($credentials['username'] === $adminRoot && $credentials['password'] === $adminPassword) {
            $request->session()->put('admin_authenticated', true);
            return response()->json(['success' => true]);
        }
        
        return response()->json(['success' => false, 'error' => 'Credenciales incorrectas'], 401);
    })->name('admin.login');
});

// Solo disponible en entorno local/desarrollo
if (app()->environment('local', 'development', 'testing')) {
    Route::prefix('admin')->name('admin.')
        ->middleware(['web', SetupRedirect::class, \App\Auth\Infrastructure\In\Http\Middleware\AdminPanelAuth::class])
        ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
        ->group(function () {
        Route::get('/', [AdminPanelController::class, 'index'])->name('dashboard');
        Route::post('/tests/run', [AdminPanelController::class, 'runTests'])->name('tests.run');
        Route::get('/database', [AdminPanelController::class, 'database'])->name('database');
        Route::get('/database/table/{table}', [AdminPanelController::class, 'tableData'])->name('database.table');
        Route::get('/logs', [AdminPanelController::class, 'logs'])->name('logs');
        Route::post('/sql', [AdminPanelController::class, 'runSql'])->name('sql.run');
        Route::post('/update', [AdminPanelController::class, 'updateApp'])->name('update');
        Route::post('/migrate', [AdminPanelController::class, 'migrate'])->name('migrate');
        Route::post('/seed', [AdminPanelController::class, 'seed'])->name('seed');
        Route::post('/cache/clear', [AdminPanelController::class, 'clearCache'])->name('cache.clear');
        Route::post('/modules/toggle', [AdminPanelController::class, 'toggleModule'])->name('modules.toggle');
        Route::get('/routes', [AdminPanelController::class, 'routes'])->name('routes');
        Route::post('/setup/credentials', [AdminPanelController::class, 'setupAdmin'])->name('setup.credentials');
        Route::get('/config', [AdminPanelController::class, 'configList'])->name('config.list');
        Route::post('/config', [AdminPanelController::class, 'configSave'])->name('config.save');
        Route::delete('/config', [AdminPanelController::class, 'configDelete'])->name('config.delete');
    });
}
