<?php

namespace App\Auth\Infrastructure\In\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Shared\Infrastructure\ConfigStore;

class SetupController
{
    public function show()
    {
        // Prefill from env if any
        $driver = env('DB_CONNECTION', 'mysql');
        $dbPath = $driver === 'sqlite'
            ? (env('DB_DATABASE') ?: base_path('database/database.sqlite'))
            : base_path('database/database.sqlite');

        return view('auth::setup', [
            'db_driver' => $driver,
            'db_host' => env('DB_HOST', '127.0.0.1'),
            'db_port' => env('DB_PORT', '3306'),
            'db_name' => env('DB_DATABASE') ?? env('DB_CATALOG_DATABASE', ''),
            'db_user' => env('DB_USERNAME', ''),
            'db_pass' => env('DB_PASSWORD', ''),
            'db_path' => $dbPath,
            'app_url' => env('APP_URL', 'http://localhost'),
        ]);
    }

    public function store(Request $request)
    {
        $driver = $request->input('db_driver', 'sqlite');
        
        $rules = [
            'db_driver' => 'required|in:mysql,sqlite',
            'db_pass' => 'nullable|string',
            'app_url' => 'required|string',
            'admin_user' => 'required|string',
            'admin_pass' => 'required|string',
            'fresh' => 'nullable|boolean',
        ];
        
        if ($driver === 'mysql') {
            $rules['db_host'] = 'required|string';
            $rules['db_port'] = 'required|string';
            $rules['db_name'] = 'required|string';
            $rules['db_user'] = 'required|string';
        } else {
            $rules['db_host'] = 'nullable|string';
            $rules['db_port'] = 'nullable|string';
            $rules['db_name'] = 'nullable|string';
            $rules['db_user'] = 'nullable|string';
            $rules['db_path'] = 'required|string';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => $validator->errors()->first(),
            ], 422);
        }

        $data = $validator->validated();

        $driver = $data['db_driver'];

        if ($driver === 'sqlite') {
            // Relax MySQL-specific requirements
            $data['db_host'] = $data['db_host'] ?? '';
            $data['db_port'] = $data['db_port'] ?? '';
            $data['db_name'] = $data['db_name'] ?? ''; // unused
            $data['db_user'] = $data['db_user'] ?? '';
            $data['db_pass'] = $data['db_pass'] ?? '';
            $data['db_path'] = $data['db_path'] ?? base_path('database/database.sqlite');
            $dir = dirname($data['db_path']);
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }
        }

        // Update runtime config to run migrations with provided DB
        if ($driver === 'mysql') {
            Config::set('database.default', 'mysql');
            Config::set('database.connections.mysql.host', $data['db_host']);
            Config::set('database.connections.mysql.port', $data['db_port']);
            Config::set('database.connections.mysql.database', $data['db_name']);
            Config::set('database.connections.mysql.username', $data['db_user']);
            Config::set('database.connections.mysql.password', $data['db_pass']);
        } else {
            Config::set('database.default', 'sqlite');
            Config::set('database.connections.sqlite.database', $data['db_path']);
            // Ensure file exists
            if (!file_exists($data['db_path'])) {
                @touch($data['db_path']);
            }
        }

        // Test connection
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => 'No se pudo conectar a la base de datos: ' . $e->getMessage()], 422);
        }

        $fresh = filter_var($data['fresh'] ?? false, FILTER_VALIDATE_BOOLEAN);

        // Run migrations (fresh by default to avoid table-exists issues in clean installs)
        try {
            if ($fresh) {
                Artisan::call('migrate:fresh', ['--force' => true]);
            } else {
                Artisan::call('migrate', ['--force' => true]);
            }
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'error' => 'Migraciones fallaron: ' . $e->getMessage()], 500);
        }

        /** @var ConfigStore $store */
        $store = app(ConfigStore::class);
        $store->set('admin.root', $data['admin_user']);
        $store->set('admin.root_password', $data['admin_pass']);
        $store->set('app.installed', true);

        // Persist to .env for next boots
        $this->writeEnv([
            'APP_URL' => $data['app_url'],
            'DB_CONNECTION' => $driver,
            'DB_HOST' => $data['db_host'] ?? '',
            'DB_PORT' => $data['db_port'] ?? '',
            'DB_DATABASE' => $driver === 'sqlite' ? $data['db_path'] : $data['db_name'],
            'DB_USERNAME' => $data['db_user'] ?? '',
            'DB_PASSWORD' => $data['db_pass'] ?? '',
            'ADMIN_ROOT' => $data['admin_user'],
            'ADMIN_ROOT_PASSWORD' => $data['admin_pass'],
            'APP_INSTALLED' => 'true',
        ]);

        // Mirror config for tests (.env.testing) so PHPUnit can target a dedicated DB
        $this->writeTestingEnv($data, $driver);

        Artisan::call('config:clear');

        return response()->json(['success' => true]);
    }

    private function writeEnv(array $pairs): void
    {
        $envPath = base_path('.env');
        $contents = file_exists($envPath) ? file_get_contents($envPath) : '';

        foreach ($pairs as $key => $value) {
            $pattern = "/^{$key}=.*$/m";
            $replacement = $key . '=' . $this->escapeEnvValue($value);

            if (preg_match($pattern, $contents)) {
                $contents = preg_replace($pattern, $replacement, $contents);
            } else {
                $contents = rtrim($contents, "\r\n") . PHP_EOL . $replacement . PHP_EOL;
            }
        }

        file_put_contents($envPath, $contents);
    }

    private function escapeEnvValue($value): string
    {
        if ($value === null) {
            return '';
        }

        $needsQuotes = str_contains((string) $value, ' ');
        $escaped = str_replace(["\n", '"'], ['\\n', '\\"'], (string) $value);

        return $needsQuotes ? '"' . $escaped . '"' : $escaped;
    }

    private function writeTestingEnv(array $data, string $driver): void
    {
        $testDb = $driver === 'sqlite'
            ? preg_replace('/\.sqlite$/', '.testing.sqlite', $data['db_path'])
            : ($data['db_name'] ? $data['db_name'] . '_test' : 'vessel_test');

        if ($driver === 'sqlite') {
            $dir = dirname($testDb);
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }
            if (!file_exists($testDb)) {
                @touch($testDb);
            }
        }

        $pairs = [
            'APP_ENV' => 'testing',
            'DB_CONNECTION' => 'testing',
            'DB_TEST_CONNECTION' => $driver,
            'DB_TEST_HOST' => $data['db_host'] ?? '',
            'DB_TEST_PORT' => $data['db_port'] ?? '',
            'DB_TEST_DATABASE' => $driver === 'sqlite' ? $testDb : $testDb,
            'DB_TEST_USERNAME' => $data['db_user'] ?? '',
            'DB_TEST_PASSWORD' => $data['db_pass'] ?? '',
        ];

        $envPath = base_path('.env.testing');
        $contents = file_exists($envPath) ? file_get_contents($envPath) : '';

        foreach ($pairs as $key => $value) {
            $pattern = "/^{$key}=.*$/m";
            $replacement = $key . '=' . $this->escapeEnvValue($value);

            if (preg_match($pattern, $contents)) {
                $contents = preg_replace($pattern, $replacement, $contents);
            } else {
                $contents = rtrim($contents, "\r\n") . PHP_EOL . $replacement . PHP_EOL;
            }
        }

        file_put_contents($envPath, $contents);
    }
}
