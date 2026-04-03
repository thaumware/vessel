<?php

use App\Auth\Infrastructure\In\Http\Controllers\AdminPanelController;
use App\Auth\Infrastructure\In\Http\Controllers\SetupController;
use App\Auth\Infrastructure\In\Http\Middleware\SetupRedirect;
use App\Shared\Infrastructure\ConfigStore;
use Illuminate\Support\Facades\Hash;
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
        $payload = $request->all();
        if ((!array_key_exists('username', $payload) || !array_key_exists('password', $payload)) && $request->getContent()) {
            $decoded = json_decode((string) $request->getContent(), true);
            if (is_array($decoded)) {
                $payload = array_merge($payload, $decoded);
            }
        }

        $inputUser = (string) ($payload['username'] ?? $payload['user'] ?? $request->getUser() ?? '');
        $inputPassword = (string) ($payload['password'] ?? $payload['pass'] ?? $request->getPassword() ?? '');

        /** @var ConfigStore $store */
        $store = app(ConfigStore::class);
        $adminRoot = (string) ($store->get('admin.root') ?? env('ADMIN_ROOT', 'admin'));
        $adminPassword = $store->get('admin.root_password') ?? env('ADMIN_ROOT_PASSWORD');
        
        if (!$adminPassword) {
            return response()->json(['success' => false, 'error' => 'Admin no configurado. Ve a /setup'], 500);
        }

        $adminPassword = (string) $adminPassword;
        $passwordOk = $inputPassword === $adminPassword
            || (str_starts_with($adminPassword, '$2y$') && Hash::check($inputPassword, $adminPassword));

        if ($inputUser === $adminRoot && $passwordOk) {
            $request->session()->put('admin_authenticated', true);
            return response()->json(['success' => true]);
        }
        
        return response()->json(['success' => false, 'error' => 'Credenciales incorrectas'], 401);
    })->name('admin.login');
});

// Admin panel routes - available in all environments
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
        Route::get('/tokens', [AdminPanelController::class, 'listTokens'])->name('tokens.list');
        Route::post('/tokens', [AdminPanelController::class, 'createToken'])->name('tokens.create');
        Route::delete('/tokens/{id}', [AdminPanelController::class, 'deleteToken'])->name('tokens.delete');
    });
