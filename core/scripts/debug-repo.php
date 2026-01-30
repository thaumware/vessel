<?php
/**
 * Script para depurar el repositorio de items
 */

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Shared\Domain\DTOs\PaginationParams;
use App\Catalog\Domain\Interfaces\ItemRepositoryInterface;
use App\Catalog\Infrastructure\Out\Models\EloquentItem;

echo "=== Debug Items Repository ===\n\n";

// Test 1: Direct Eloquent count
$directCount = EloquentItem::count();
echo "EloquentItem::count() = {$directCount}\n";

// Test 2: Query count
$queryCount = EloquentItem::query()->count();
echo "EloquentItem::query()->count() = {$queryCount}\n";

// Test 3: DB raw count
$dbCount = DB::table('catalog_items')->whereNull('deleted_at')->count();
echo "DB::table('catalog_items')->whereNull('deleted_at')->count() = {$dbCount}\n";

// Test 4: Repository findAll
$repository = app(ItemRepositoryInterface::class);
$params = new PaginationParams(page: 1, perPage: 50);
$result = $repository->findAll($params);

echo "\nRepository findAll result:\n";
echo "  total: {$result->total}\n";
echo "  page: {$result->page}\n";
echo "  perPage: {$result->perPage}\n";
echo "  lastPage: {$result->lastPage}\n";
echo "  data count: " . count($result->data) . "\n";

// Test 5: Check if items have names
if (count($result->data) > 0) {
    echo "\nFirst 5 items:\n";
    foreach (array_slice($result->data, 0, 5) as $item) {
        echo "  - {$item->getName()} (ID: {$item->getId()})\n";
    }
}

// Test 6: Check database location
echo "\nDatabase connection info:\n";
$dbPath = config('database.connections.sqlite.database');
echo "  SQLite path: {$dbPath}\n";
echo "  File exists: " . (file_exists($dbPath) ? 'Yes' : 'No') . "\n";

echo "\n=== Done ===\n";
