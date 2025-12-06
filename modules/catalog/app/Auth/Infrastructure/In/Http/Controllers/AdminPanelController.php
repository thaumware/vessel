<?php

declare(strict_types=1);

namespace App\Auth\Infrastructure\In\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\Process\Process;
use App\Shared\Infrastructure\ModuleRegistry;
use App\Shared\Infrastructure\ConfigStore;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Str;

class AdminPanelController
{
    /**
     * Prefijos de tablas que pertenecen al modulo Catalog
     */
    private const CATALOG_TABLE_PREFIXES = [
        'catalog_',
        'stock_',
        'locations_',
        'uom_',
        'taxonomy_',
        'pricing_',
        'portal',
        'sessions',
    ];

    /**
     * Panel principal de administración
     */
    public function index(): View
    {
        $database = $this->getDatabaseInfo();
        $seeders = $this->getAvailableSeeders();
        $modules = $this->getModuleStatuses();
        $missingTables = $this->getMissingCoreTables();
        $needsAdminSetup = (bool) request()->attributes->get('needs_admin_setup', false);
        if (!empty($missingTables)) {
            // No tiene sentido pedir credenciales mientras faltan tablas base
            $needsAdminSetup = false;
        }
        $configEntries = $this->getConfigEntries();
        
        // Formatear tablas para la vista
        $tables = array_map(function ($table) {
            return [
                'name' => $table['name'],
                'rows' => $table['rows_count'],
            ];
        }, $database['tables']);
        
        return view('auth::dashboard', [
            'database' => $database,
            'tables' => $tables,
            'seeders' => $seeders,
            'modules' => $modules,
            'missingTables' => $missingTables,
            'needsAdminSetup' => $needsAdminSetup,
            'configEntries' => $configEntries,
        ]);
    }

    /**
     * Parsear linea de log en formato Laravel
     */
    private function parseLogLine(string $line): array
    {
        if (preg_match('/\[(.*?)\]\s+([\w\.]+)\.([A-Z]+):\s+(.*)/', $line, $matches)) {
            return [
                'timestamp' => $matches[1],
                'env' => $matches[2],
                'level' => strtolower($matches[3]),
                'message' => $matches[4],
                'raw' => $line,
            ];
        }

        return [
            'timestamp' => null,
            'env' => null,
            'level' => 'info',
            'message' => $line,
            'raw' => $line,
        ];
    }

    private function getConfigEntries(): array
    {
        /** @var ConfigStore $store */
        $store = app(ConfigStore::class);
        return array_map(function ($entry) {
            $key = $entry['key'] ?? '';
            $value = $entry['value'] ?? null;

            if ($this->isSensitiveKey($key)) {
                $value = '******';
            }

            return [
                'key' => $key,
                'value' => $value,
            ];
        }, $store->all());
    }

    /**
     * Extraer tablas referenciadas en un SELECT (from/join)
     */
    private function extractTablesFromQuery(string $query): array
    {
        $tables = [];

        if (preg_match_all('/\b(from|join)\s+([a-zA-Z0-9_\.]+)/i', $query, $matches)) {
            $tables = $matches[2];
        }

        return array_values(array_unique($tables));
    }
    
    /**
     * Obtener seeders disponibles
     */
    private function getAvailableSeeders(): array
    {
        return [
            \App\Uom\Infrastructure\Out\Database\Seeders\UomSeeder::class,
        ];
    }

    /**
     * Ejecutar tests de un módulo específico
     */
    public function runTests(Request $request): JsonResponse
    {
        $filter = $request->input('filter', '');
        
        // Usar php para ejecutar phpunit (compatible con Windows y Linux)
        $phpunit = base_path('vendor/bin/phpunit');
        $command = ['php', $phpunit, '--colors=never'];
        
        // Parsear el filtro que viene del frontend (ej: "--filter Locations")
        if (!empty($filter)) {
            $parts = explode(' ', trim($filter), 2);
            if (count($parts) === 2 && $parts[0] === '--filter') {
                $command[] = '--filter=' . $parts[1];
            }
        }
        
        try {
            $process = new Process($command, base_path());
            $process->setTimeout(300);
            $process->run();
            
            $output = $process->getOutput() . $process->getErrorOutput();
            $success = $process->isSuccessful();
            
            $results = $this->parseTestOutput($output);
            
            return response()->json([
                'success' => $success,
                'output' => $output,
                'results' => $results,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'output' => 'Error: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function configList(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'entries' => $this->getConfigEntries(),
        ]);
    }

    public function configSave(Request $request): JsonResponse
    {
        $key = trim((string) $request->input('key', ''));
        $value = $request->input('value');

        if ($key === '') {
            return response()->json(['success' => false, 'error' => 'Key is required'], 422);
        }

        /** @var ConfigStore $store */
        $store = app(ConfigStore::class);
        $store->set($key, $value);

        return response()->json([
            'success' => true,
            'entries' => $this->getConfigEntries(),
        ]);
    }

    public function configDelete(Request $request): JsonResponse
    {
        $key = trim((string) $request->input('key', ''));
        if ($key === '') {
            return response()->json(['success' => false, 'error' => 'Key is required'], 422);
        }

        /** @var ConfigStore $store */
        $store = app(ConfigStore::class);
        $store->delete($key);

        return response()->json([
            'success' => true,
            'entries' => $this->getConfigEntries(),
        ]);
    }

    private function isSensitiveKey(string $key): bool
    {
        $keyLower = strtolower($key);
        $needles = ['password', 'secret', 'token', 'key'];
        foreach ($needles as $needle) {
            if (str_contains($keyLower, $needle)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Ver estructura de la base de datos
     */
    public function database(): JsonResponse
    {
        $tables = $this->getDatabaseTables();
        
        return response()->json([
            'tables' => $tables,
            'connection' => config('database.default'),
        ]);
    }

    /**
     * Ver contenido de una tabla
     */
    public function tableData(string $table): JsonResponse
    {
        if (!$this->isAllowedTable($table)) {
            return response()->json(['error' => 'Table not allowed'], 403);
        }
        
        if (!Schema::hasTable($table)) {
            return response()->json(['error' => 'Table not found'], 404);
        }
        
        $columns = Schema::getColumnListing($table);
        $data = DB::table($table)->limit(100)->get();
        $count = DB::table($table)->count();
        
        return response()->json([
            'table' => $table,
            'columns' => $columns,
            'data' => $data,
            'total_count' => $count,
            'showing' => min(100, $count),
        ]);
    }

    /**
     * Ver ultimas entradas del log de Laravel con filtros simples
     */
    public function logs(Request $request): JsonResponse
    {
        $level = strtolower((string) $request->input('level', ''));
        $search = strtolower((string) $request->input('search', ''));
        $limit = (int) $request->input('limit', 200);
        $limit = max(10, min($limit, 500));

        $logFile = storage_path('logs/laravel.log');
        if (!file_exists($logFile)) {
            return response()->json([
                'success' => false,
                'error' => 'Log file not found',
                'entries' => [],
            ], 404);
        }

        $lines = @file($logFile, FILE_IGNORE_NEW_LINES) ?: [];
        $lines = array_slice($lines, -2000);

        $entries = [];
        $current = null;

        foreach (array_reverse($lines) as $line) {
            $isHeader = preg_match('/^\[(.*?)\]\s+[\w\.]+\.[A-Z]+:/', $line) === 1;

            if ($isHeader) {
                if ($current) {
                    $entries[] = $current;
                    if (count($entries) >= $limit) {
                        break;
                    }
                }
                $current = $this->parseLogLine($line);
            } else {
                if ($current) {
                    $current['message'] = $line . "\n" . $current['message'];
                    $current['raw'] = $line . "\n" . $current['raw'];
                }
            }
        }

        if ($current && count($entries) < $limit) {
            $entries[] = $current;
        }

        // Apply filters after grouping
        $entries = array_values(array_filter($entries, function ($entry) use ($level, $search) {
            if ($level && ($entry['level'] ?? '') !== $level) {
                return false;
            }
            if ($search && !str_contains(strtolower($entry['raw'] ?? ''), $search)) {
                return false;
            }
            return true;
        }));

        return response()->json([
            'success' => true,
            'entries' => array_slice($entries, 0, $limit),
            'available' => count($lines),
        ]);
    }

    /**
     * Ejecutar una consulta SQL de solo lectura (SELECT) sobre tablas permitidas
     */
    public function runSql(Request $request): JsonResponse
    {
        $query = trim((string) $request->input('query', ''));

        if ($query === '') {
            return response()->json([
                'success' => false,
                'error' => 'Query is required',
            ], 400);
        }

        if (!preg_match('/^select\s/i', $query) || preg_match('/\b(drop|delete|update|insert|alter|create|truncate|rename)\b/i', $query)) {
            return response()->json([
                'success' => false,
                'error' => 'Only SELECT statements are allowed',
            ], 400);
        }

        $tables = $this->extractTablesFromQuery($query);
        foreach ($tables as $table) {
            if (!$this->isAllowedTable($table)) {
                return response()->json([
                    'success' => false,
                    'error' => "Table '{$table}' is not allowed",
                ], 403);
            }
        }

        if (stripos($query, ' limit ') === false) {
            $query .= ' LIMIT 100';
        }

        try {
            $start = microtime(true);
            $rows = DB::select($query);
            $durationMs = round((microtime(true) - $start) * 1000, 2);
            $columns = empty($rows) ? [] : array_keys((array) $rows[0]);

            return response()->json([
                'success' => true,
                'rows' => $rows,
                'columns' => $columns,
                'count' => count($rows),
                'duration_ms' => $durationMs,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Ejecutar migraciones
     */
    public function migrate(Request $request): JsonResponse
    {
        $action = $request->input('action', 'migrate');
        
        try {
            switch ($action) {
                case 'migrate':
                    Artisan::call('migrate', ['--force' => true]);
                    break;
                case 'fresh':
                    Artisan::call('migrate:fresh', ['--force' => true]);
                    $this->setInstalledFlag(false);
                    break;
                case 'rollback':
                    Artisan::call('migrate:rollback', ['--force' => true]);
                    break;
                case 'seed':
                    Artisan::call('db:seed', ['--force' => true]);
                    break;
                default:
                    return response()->json(['error' => 'Invalid action'], 400);
            }
            
            $output = Artisan::output();
            
            return response()->json([
                'success' => true,
                'action' => $action,
                'output' => $output,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function setInstalledFlag(bool $installed): void
    {
        try {
            /** @var ConfigStore $store */
            $store = app(ConfigStore::class);
            $store->set('app.installed', $installed);
        } catch (\Throwable $e) {
            // best effort only
        }
    }

    /**
     * Ejecutar actualizacion automatica (git pull + composer + migrate)
     */
    public function updateApp(Request $request): JsonResponse
    {
        $branch = $request->input('branch');
        $options = [];
        if ($branch) {
            $options['--branch'] = $branch;
        }

        try {
            Artisan::call('app:update', $options);
            $output = Artisan::output();

            return response()->json([
                'success' => true,
                'output' => $output,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Estado de las migraciones
     */
    public function migrationsStatus(): JsonResponse
    {
        try {
            Artisan::call('migrate:status');
            $output = Artisan::output();
            
            return response()->json([
                'output' => $output,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Ejecutar seeders específicos
     */
    public function seed(Request $request): JsonResponse
    {
        $seeder = $request->input('seeder');
        
        try {
            $options = ['--force' => true];
            if ($seeder) {
                $options['--class'] = $seeder;
            }
            
            Artisan::call('db:seed', $options);
            $output = Artisan::output();
            
            return response()->json([
                'success' => true,
                'output' => $output,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Limpiar cache
     */
    public function clearCache(): JsonResponse
    {
        try {
            $output = [];
            
            Artisan::call('cache:clear');
            $output[] = Artisan::output();
            
            Artisan::call('config:clear');
            $output[] = Artisan::output();
            
            Artisan::call('route:clear');
            $output[] = Artisan::output();
            
            Artisan::call('view:clear');
            $output[] = Artisan::output();
            
            return response()->json([
                'success' => true,
                'output' => implode("\n", $output),
                'message' => 'All caches cleared',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'output' => 'Error: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verificar si una tabla esta permitida (pertenece al modulo Catalog)
     */
    private function isAllowedTable(string $table): bool
    {
        // Si tiene esquema (ej: vessel_catalog.catalog_items), extraer solo el nombre
        $tableName = $table;
        if (str_contains($table, '.')) {
            $parts = explode('.', $table);
            $tableName = end($parts);
        }
        
        // Verificar prefijos del catalogo
        foreach (self::CATALOG_TABLE_PREFIXES as $prefix) {
            if (str_starts_with($tableName, $prefix)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Obtener informacion de base de datos
     */
    private function getDatabaseInfo(): array
    {
        $connection = config('database.default');
        $tables = $this->getDatabaseTables();
        
        return [
            'connection' => $connection,
            'database' => config("database.connections.{$connection}.database"),
            'tables_count' => count($tables),
            'tables' => $tables,
        ];
    }

    /**
     * Obtener solo tablas del modulo Catalog desde la base de datos actual
     */
    private function getDatabaseTables(): array
    {
        try {
            $tables = [];
            $connection = config('database.default');
            $database = config("database.connections.{$connection}.database");
            
            // Obtener tablas de la base de datos actual
            $allTables = DB::select('SHOW TABLES');
            $key = "Tables_in_{$database}";
            
            foreach ($allTables as $tableObj) {
                $tableName = $tableObj->$key ?? null;
                
                if (!$tableName || !$this->isAllowedTable($tableName)) {
                    continue;
                }
                
                try {
                    $columns = Schema::getColumnListing($tableName);
                    $count = DB::table($tableName)->count();
                    
                    $tables[] = [
                        'name' => $tableName,
                        'columns' => $columns,
                        'columns_count' => count($columns),
                        'rows_count' => $count,
                    ];
                } catch (\Exception $e) {
                    // Ignorar tablas con errores
                    continue;
                }
            }
            
            // Ordenar alfabeticamente
            usort($tables, fn($a, $b) => strcmp($a['name'], $b['name']));
            
            return $tables;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Obtener test suites disponibles
     */
    private function getTestSuites(): array
    {
        return [
            ['name' => 'all', 'label' => 'Todos los tests'],
            ['name' => 'Items', 'label' => 'Items Module'],
            ['name' => 'Stock', 'label' => 'Stock Module'],
            ['name' => 'Locations', 'label' => 'Locations Module'],
            ['name' => 'Uom', 'label' => 'UoM Module'],
            ['name' => 'Taxonomy', 'label' => 'Taxonomy Module'],
            ['name' => 'Auth', 'label' => 'Auth Module'],
            ['name' => 'Feature', 'label' => 'Feature Tests'],
            ['name' => 'Integration', 'label' => 'Integration Tests'],
        ];
    }

    /**
     * Parsear output de PHPUnit
     */
    private function parseTestOutput(string $output): array
    {
        $results = [
            'tests' => 0,
            'assertions' => 0,
            'failures' => 0,
            'errors' => 0,
            'skipped' => 0,
            'passed' => true,
        ];
        
        if (preg_match('/OK \((\d+) tests?, (\d+) assertions?\)/', $output, $matches)) {
            $results['tests'] = (int) $matches[1];
            $results['assertions'] = (int) $matches[2];
            $results['passed'] = true;
        }
        
        if (preg_match('/Tests: (\d+), Assertions: (\d+)(?:, Failures: (\d+))?(?:, Errors: (\d+))?/', $output, $matches)) {
            $results['tests'] = (int) $matches[1];
            $results['assertions'] = (int) $matches[2];
            $results['failures'] = (int) ($matches[3] ?? 0);
            $results['errors'] = (int) ($matches[4] ?? 0);
            $results['passed'] = ($results['failures'] + $results['errors']) === 0;
        }
        
        return $results;
    }

    /**
     * Estado de modulos (habilitado/ws) basado en config/env.
     */
    private function getModuleStatuses(): array
    {
        /** @var ModuleRegistry $registry */
        $registry = app(ModuleRegistry::class);
        $config = config('modules.modules', []);
        /** @var Migrator $migrator */
        $migrator = app('migrator');

        $result = [];
        foreach ($config as $name => $settings) {
            $provider = $settings['provider'] ?? null;
            $installed = $provider && class_exists($provider);
            $loaded = $installed && !empty(app()->getProviders($provider));

            $pendingMigrations = null;
            if (!empty($settings['migrations_path']) && is_dir($settings['migrations_path']) && $migrator->repositoryExists()) {
                try {
                    $files = $migrator->getMigrationFiles([$settings['migrations_path']]);
                    $ran = $migrator->getRepository()->getRan();
                    $pendingMigrations = array_values(array_diff(array_keys($files), $ran));
                } catch (\Throwable $e) {
                    $pendingMigrations = null;
                }
            }

            $result[] = [
                'name' => $name,
                'enabled' => $registry->enabled($name),
                'ws_enabled' => $registry->wsEnabled($name),
                'provider' => $provider,
                'installed' => $installed,
                'loaded' => $loaded,
                'pending_migrations' => is_array($pendingMigrations) ? count($pendingMigrations) : null,
            ];
        }

        return $result;
    }

    private function getMissingCoreTables(): array
    {
        $required = ['shared_config', 'portal_origins', 'portals'];
        $missing = [];

        foreach ($required as $table) {
            try {
                if (!Schema::hasTable($table)) {
                    $missing[] = $table;
                }
            } catch (\Throwable $e) {
                $missing[] = $table;
            }
        }

        return $missing;
    }

    /**
     * List routes with module inference and filters.
     */
    public function routes(Request $request): JsonResponse
    {
        $moduleFilter = $request->input('module');
        $methodFilter = strtoupper((string) $request->input('method', ''));
        $search = strtolower((string) $request->input('search', ''));

        $modulesConfig = config('modules.modules', []);
        $namespaces = [];
        foreach ($modulesConfig as $name => $settings) {
            $provider = $settings['provider'] ?? '';
            $ns = $this->extractNamespaceFromProvider($provider);
            if ($ns) {
                $namespaces[$name] = $ns;
            }
        }

        $routes = [];
        foreach (app('router')->getRoutes() as $route) {
            $action = $route->getActionName() ?: 'Closure';
            $uri = $route->uri();
            $methods = array_values(array_diff($route->methods(), ['HEAD']));
            $name = $route->getName();
            $middleware = $route->middleware();

            $moduleName = $this->matchModuleByAction($action, $namespaces) ?? 'shared';

            if ($moduleFilter && $moduleName !== $moduleFilter) {
                continue;
            }

            if ($methodFilter && !in_array($methodFilter, $methods, true)) {
                continue;
            }

            if ($search) {
                $haystack = strtolower($uri . ' ' . ($name ?? '') . ' ' . $action);
                if (!str_contains($haystack, $search)) {
                    continue;
                }
            }

            $routes[] = [
                'uri' => $uri,
                'methods' => $methods,
                'name' => $name,
                'action' => $action,
                'middleware' => $middleware,
                'module' => $moduleName,
            ];
        }

        return response()->json([
            'success' => true,
            'routes' => $routes,
            'modules' => array_keys($modulesConfig),
        ]);
    }

    private function extractNamespaceFromProvider(?string $provider): ?string
    {
        if (!$provider) {
            return null;
        }

        // Example: App\\Stock\\Infrastructure\\StockServiceProvider => App\\Stock
        $parts = explode('\\', trim($provider, '\\'));
        if (count($parts) < 2) {
            return null;
        }

        return implode('\\', array_slice($parts, 0, 2));
    }

    private function matchModuleByAction(string $action, array $namespaces): ?string
    {
        foreach ($namespaces as $module => $ns) {
            if (Str::startsWith($action, $ns)) {
                return $module;
            }
        }

        return null;
    }

    /**
     * Toggle module on/off (and websockets) by updating config/env.
     */
    public function toggleModule(Request $request): JsonResponse
    {
        $module = (string) $request->input('module');
        $enabled = filter_var($request->input('enabled', true), FILTER_VALIDATE_BOOLEAN);
        $wsEnabled = $request->has('ws_enabled')
            ? filter_var($request->input('ws_enabled'), FILTER_VALIDATE_BOOLEAN)
            : null;

        $config = config('modules.modules', []);
        if (!isset($config[$module])) {
            return response()->json(['success' => false, 'error' => 'Module not found'], 404);
        }

        /** @var ModuleRegistry $registry */
        $registry = app(ModuleRegistry::class);
        /** @var ConfigStore $store */
        $store = app(ConfigStore::class);

        $registry->setEnabled($module, $enabled);
        $store->set("modules.{$module}.enabled", $enabled);
        if ($wsEnabled !== null) {
            $registry->setWsEnabled($module, $wsEnabled);
            $store->set("modules.{$module}.ws_enabled", $wsEnabled);
        }

        return response()->json([
            'success' => true,
            'modules' => $this->getModuleStatuses(),
        ]);
    }

    public function setupAdmin(Request $request): JsonResponse
    {
        $user = trim((string) $request->input('user'));
        $pass = (string) $request->input('password');

        if ($user === '' || $pass === '') {
            return response()->json(['success' => false, 'error' => 'Usuario y password son requeridos'], 422);
        }

        /** @var ConfigStore $store */
        $store = app(ConfigStore::class);
        $store->set('admin.root', $user);
        $store->set('admin.root_password', $pass);

        return response()->json(['success' => true]);
    }
}
