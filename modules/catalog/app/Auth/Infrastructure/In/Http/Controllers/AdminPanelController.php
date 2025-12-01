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
        ]);
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
}
