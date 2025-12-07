<?php

namespace App\Auth\Infrastructure\In\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Shared\Infrastructure\ConfigStore;

class SetupRedirect
{
    public function handle(Request $request, Closure $next)
    {
        if ($this->isInstalled()) {
            return $next($request);
        }

        if ($request->is('setup') || $request->is('setup/*')) {
            return $next($request);
        }

        return redirect('/setup');
    }

    private function isInstalled(): bool
    {
        // Env flag wins to avoid DB access loops
        $envFlag = env('APP_INSTALLED');
        if ($envFlag !== null) {
            return filter_var($envFlag, FILTER_VALIDATE_BOOLEAN);
        }

        try {
            /** @var ConfigStore $store */
            $store = app(ConfigStore::class);
            return filter_var($store->get('app.installed', false), FILTER_VALIDATE_BOOLEAN);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
