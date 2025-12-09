<?php

namespace App\Auth\Infrastructure\In\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Shared\Infrastructure\ConfigStore;

class SetupController
{
    public function show()
    {
        // Si no existe .env, crearlo desde .env.example
        $envPath = base_path('.env');
        $envExamplePath = base_path('.env.example');
        
        if (!file_exists($envPath) && file_exists($envExamplePath)) {
            copy($envExamplePath, $envPath);
            
            // Generar APP_KEY
            Artisan::call('key:generate', ['--force' => true]);
        }
        
        // Bloquear si ya está instalado
        if (env('APP_INSTALLED') === 'true' || env('APP_INSTALLED') === true) {
            return redirect('/admin')->with('error', 'La aplicación ya está instalada.');
        }

        // Prefill from env if any
        $driver = env('DB_CONNECTION', 'mysql');
        $dbPath = $driver === 'sqlite'
            ? (env('DB_DATABASE') ?: base_path('database/database.sqlite'))
            : base_path('database/database.sqlite');

        // Detectar si está en Docker y sugerir host.docker.internal
        $defaultHost = '127.0.0.1';
        if (file_exists('/.dockerenv') || getenv('DOCKER_ENV')) {
            $defaultHost = 'host.docker.internal';
        }

        return view('auth::setup', [
            'db_driver' => $driver,
            'db_host' => env('DB_HOST', $defaultHost),
            'db_port' => env('DB_PORT', '3307'),
            'db_name' => env('DB_DATABASE', 'vessel_db'),
            'db_user' => env('DB_USERNAME', 'root'),
            'db_pass' => env('DB_PASSWORD', ''),
            'db_path' => $dbPath,
            'app_url' => env('APP_URL', 'http://localhost'),
        ]);
    }

    public function store(Request $request)
    {
        try {
            $driver = $request->input('db_driver', 'sqlite');
            
            // Log raw input para debug
            Log::info('Setup Request Received', [
                'all' => $request->all(),
                'driver' => $driver,
            ]);
            
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
                Config::set('database.connections.mysql.port', (int)$data['db_port']);
                Config::set('database.connections.mysql.database', $data['db_name']);
                Config::set('database.connections.mysql.username', $data['db_user']);
                Config::set('database.connections.mysql.password', $data['db_pass'] ?? '');
                Config::set('database.connections.mysql.unix_socket', '');
                
                // Log para debug
                Log::info('MySQL Config', [
                    'host' => $data['db_host'],
                    'port' => $data['db_port'],
                    'database' => $data['db_name'],
                    'username' => $data['db_user'],
                    'password_empty' => empty($data['db_pass']),
                ]);
                
                // Limpiar conexiones cacheadas
                DB::purge('mysql');
            } else {
                Config::set('database.default', 'sqlite');
                Config::set('database.connections.sqlite.database', $data['db_path']);
                // Ensure file exists
                if (!file_exists($data['db_path'])) {
                    @touch($data['db_path']);
                }
                
                // Limpiar conexiones cacheadas
                DB::purge('sqlite');
            }

            // NO usar DB::reconnect() - crear nueva conexión desde cero

            // Test connection - forzar conexión específica
            try {
                if ($driver === 'mysql') {
                    // Purge again to ensure clean state
                    DB::purge('mysql');
                    
                    // Log config actual antes de conectar
                    $actualConfig = Config::get('database.connections.mysql');
                    Log::info('Config actual antes de conectar', $actualConfig);

                    // Configurar timeout corto para evitar 504
                     Config::set('database.connections.mysql.options', [
                        \PDO::ATTR_TIMEOUT => 5,
                        \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
                    ]);
                    
                    $pdo = DB::connection('mysql')->getPdo();
                    Log::info('Conexión exitosa');
                } else {
                    DB::purge('sqlite');
                    DB::connection('sqlite')->getPdo();
                }
            } catch (\Throwable $e) {
                Log::error('Error en test de conexión', [
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                ]);
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
                Log::error('Migration Error', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                return response()->json(['success' => false, 'error' => 'Migraciones fallaron: ' . $e->getMessage()], 500);
            }

            // Hashear password del admin
            $hashedPassword = password_hash($data['admin_pass'], PASSWORD_BCRYPT);

            /** @var ConfigStore $store */
            try {
                $store = app(ConfigStore::class);
                $store->set('admin.root', $data['admin_user']);
                $store->set('admin.root_password', $hashedPassword);
                $store->set('app.installed', true);
            } catch (\Throwable $e) {
                Log::error('ConfigStore Error', ['message' => $e->getMessage()]);
                // Ignorar error de config store y seguir con .env
            }

            // Persist to .env for next boots con password hasheado
            try {
                $this->writeEnv([
                    'APP_URL' => $data['app_url'],
                    'DB_CONNECTION' => $driver,
                    'DB_HOST' => $data['db_host'] ?? '',
                    'DB_PORT' => $data['db_port'] ?? '',
                    'DB_DATABASE' => $driver === 'sqlite' ? $data['db_path'] : $data['db_name'],
                    'DB_USERNAME' => $data['db_user'] ?? '',
                    'DB_PASSWORD' => $data['db_pass'] ?? '',
                    'ADMIN_ROOT' => $data['admin_user'],
                    'ADMIN_ROOT_PASSWORD' => $hashedPassword,
                    'APP_INSTALLED' => 'true',
                    'APP_DEBUG' => 'true', // Force debug for now
                ]);
            } catch (\Throwable $e) {
                Log::error('WriteEnv Error', ['message' => $e->getMessage()]);
                return response()->json(['success' => false, 'error' => 'No se pudo escribir el archivo .env: ' . $e->getMessage()], 500);
            }

            // Mirror config for tests (.env.testing) so PHPUnit can target a dedicated DB
            $this->writeTestingEnv($data, $driver);

            Artisan::call('config:clear');

            return response()->json(['success' => true]);

        } catch (\Throwable $e) {
            Log::critical('Unhandled Exception in SetupController::store', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'DEBUG ERROR: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ], 500);
        }
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

        $str = (string) $value;
        
        // Si contiene $ (como hashes bcrypt), espacios, o caracteres especiales, usar comillas
        $needsQuotes = str_contains($str, ' ') || str_contains($str, '$') || str_contains($str, '#');
        
        if ($needsQuotes) {
            // Escapar comillas dobles y backslashes
            $escaped = str_replace(['\\', '"'], ['\\\\', '\\"'], $str);
            return '"' . $escaped . '"';
        }

        return $str;
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
