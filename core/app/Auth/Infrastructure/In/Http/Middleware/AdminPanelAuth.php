<?php

namespace App\Auth\Infrastructure\In\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Shared\Infrastructure\ConfigStore;
use Illuminate\Support\Facades\Hash;

class AdminPanelAuth
{
    public function handle(Request $request, Closure $next)
    {
        /** @var ConfigStore $store */
        $store = app(ConfigStore::class);

        // Check session first
        if (session('admin_authenticated')) {
            return $next($request);
        }

        // Try basic auth for backward compat
        $user = $store->get('admin.root')
            ?? env('ADMIN_ROOT')
            ?? env('ADMIN_USERNAME')
            ?? 'admin';

        $pass = $store->get('admin.root_password')
            ?? env('ADMIN_ROOT_PASSWORD')
            ?? env('ADMIN_PASSWORD')
            ?? 'admin123';

        [$reqUser, $reqPass] = $this->resolveCredentials($request);

        $credentialsOk = $reqUser === $user
            && ($reqPass === $pass || (str_starts_with((string) $pass, '$2y$') && Hash::check((string) $reqPass, (string) $pass)));

        if ($credentialsOk) {
            session(['admin_authenticated' => true]);
            return $next($request);
        }

        // Show login form instead of ugly browser prompt
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return response()->view('auth::login', [
            'user' => $user,
        ], 401);
    }

    private function resolveCredentials(Request $request): array
    {
        if ($request->attributes->has('basic_user')) {
            return [
                $request->attributes->get('basic_user'),
                $request->attributes->get('basic_pass'),
            ];
        }

        $user = $request->getUser();
        $pass = $request->getPassword();

        if ($user && $pass !== null) {
            return [$user, $pass];
        }

        $auth = $request->header('Authorization');
        if ($auth && str_starts_with($auth, 'Basic ')) {
            $decoded = base64_decode(substr($auth, 6)) ?: '';
            if (str_contains($decoded, ':')) {
                [$u, $p] = explode(':', $decoded, 2);
                return [$u, $p];
            }
        }

        return [$user, $pass];
    }
}
