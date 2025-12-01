<?php

namespace App\Admin\Infrastructure\In\Http\Controllers;

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
     * Tablas que pertenecen al modulo Catalog
     */
    private const CATALOG_TABLES = [
        'items',
        'item_identifiers',
        'item_variants',
        'item_terms',
        'locations',
        'addresses',
        'stock_items',
        'stocks',
        'movements',
        'batches',
        'units',
        'uom_categories',
        'uom_measures',
        'uom_conversions',
        'vocabularies',
        'terms',
        'term_relations',
        'prices',
        'price_lists',
        'migrations',
    ];

    /**
     * Panel principal de administración
     */
    public function index(): View
    {
        $modules = $this->getModulesStatus();
        $database = $this->getDatabaseInfo();
        $testSuites = $this->getTestSuites();
        
        return view('admin::dashboard', [
            'modules' => $modules,
            'database' => $database,
            'testSuites' => $testSuites,
        ]);
    }

    /**
     * Ejecutar tests de un módulo específico
     */
    public function runTests(Request $request): JsonResponse
    {
        $suite = $request->input('suite', 'all');
        $filter = $request->input('filter');
        
        $command = ['./vendor/bin/phpunit', '--colors=never'];
        
        if ($suite !== 'all') {
            $command[] = '--testsuite=' . $suite;
        }
        
        if ($filter) {
            $command[] = '--filter=' . $filter;
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
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');
            
            return response()->json([
                'success' => true,
                'message' => 'All caches cleared',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verificar si una tabla esta permitida
     */
    private function isAllowedTable(string $table): bool
    {
        return in_array($table, self::CATALOG_TABLES, true);
    }

    /**
     * Obtener estado de modulos
     */
    private function getModulesStatus(): array
    {
        $modules = [
            'Items' => [
                'path' => 'app/Items',
                'provider' => 'App\Items\Infrastructure\ItemsServiceProvider',
                'description' => 'Catalogo de productos',
            ],
            'Locations' => [
                'path' => 'app/Locations',
                'provider' => 'App\Locations\Infrastructure\LocationsServiceProvider',
                'description' => 'Ubicaciones y direcciones',
            ],
            'Stock' => [
                'path' => 'app/Stock',
                'provider' => 'App\Stock\Infrastructure\StockServiceProvider',
                'description' => 'Gestion de inventario',
            ],
            'Uom' => [
                'path' => 'app/Uom',
                'provider' => 'App\Uom\Infrastructure\UomServiceProvider',
                'description' => 'Unidades de medida',
            ],
            'Taxonomy' => [
                'path' => 'app/Taxonomy',
                'provider' => 'App\Taxonomy\Infrastructure\TaxonomyServiceProvider',
                'description' => 'Categorias y taxonomias',
            ],
            'Pricing' => [
                'path' => 'app/Pricing',
                'provider' => 'App\Pricing\Infrastructure\PricingServiceProvider',
                'description' => 'Precios y tarifas',
            ],
        ];
        
        foreach ($modules as $name => &$info) {
            $info['exists'] = is_dir(base_path($info['path']));
            $info['has_tests'] = is_dir(base_path($info['path'] . '/Tests'));
            $info['has_domain'] = is_dir(base_path($info['path'] . '/Domain'));
            $info['has_application'] = is_dir(base_path($info['path'] . '/Application'));
            $info['has_infrastructure'] = is_dir(base_path($info['path'] . '/Infrastructure'));
        }
        
        return $modules;
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
     * Obtener solo tablas del modulo Catalog
     */
    private function getDatabaseTables(): array
    {
        try {
            $tables = [];
            $tableNames = Schema::getTableListing();
            
            foreach ($tableNames as $tableName) {
                if (!$this->isAllowedTable($tableName)) {
                    continue;
                }
                
                $columns = Schema::getColumnListing($tableName);
                $count = DB::table($tableName)->count();
                
                $tables[] = [
                    'name' => $tableName,
                    'columns' => $columns,
                    'columns_count' => count($columns),
                    'rows_count' => $count,
                ];
            }
            
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
            ['name' => 'Admin', 'label' => 'Admin Module'],
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
