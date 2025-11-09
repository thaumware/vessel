<?php

require_once __DIR__ . '/../src/Contracts/StorageAdapter.php';
require_once __DIR__ . '/../src/Contracts/DataFetcher.php';
require_once __DIR__ . '/../src/Core/PortalRegistry.php';
require_once __DIR__ . '/../src/Core/RelationLoader.php';
require_once __DIR__ . '/MockAdapter.php';

use Thaumware\Portal\Tests\MockAdapter;
use Thaumware\Portal\Core\PortalRegistry;
use Thaumware\Portal\Core\RelationLoader;

class PortalTest
{
    private MockAdapter $adapter;
    private PortalRegistry $registry;
    private RelationLoader $loader;

    public function __construct()
    {
        $this->adapter = new MockAdapter();
        $this->registry = new PortalRegistry($this->adapter);
        $this->loader = new RelationLoader($this->adapter, $this->adapter);
    }

    public function testRegisterOrigin(): void
    {
        $originId = $this->registry->register('items', 'catalog_items', 'table');
        
        assert(!empty($originId), 'Origin ID should not be empty');
        echo "✓ Register origin: {$originId}\n";
    }

    public function testLinkModels(): void
    {
        $originId = $this->registry->register('items', 'catalog_items', 'table');
        
        $this->registry->link('term-1', 'Term', 'items', 'item-1', ['custom' => 'data']);
        
        echo "✓ Link models\n";
    }

    public function testAttachRelations(): void
    {
        $originId = $this->registry->register('items', 'catalog_items', 'table');
        
        $this->registry->link('term-1', 'Term', 'items', 'item-1', null);
        $this->registry->link('term-1', 'Term', 'items', 'item-2', null);
        
        $models = [
            (object)['id' => 'term-1', 'name' => 'Technology'],
        ];
        
        $result = $this->loader->attach($models);
        
        assert(isset($result[0]->portal_items), 'Should have portal_items property');
        echo "✓ Attach relations: " . json_encode($result[0]->portal_items) . "\n";
    }

    public function testDeactivateOrigin(): void
    {
        $this->registry->register('temp', 'temp_table', 'table');
        $this->registry->deactivate('temp');
        
        $origin = $this->adapter->findOriginByName('temp');
        assert($origin === null, 'Deactivated origin should not be found');
        
        echo "✓ Deactivate origin\n";
    }

    public function runAll(): void
    {
        echo "Running Portal Tests...\n\n";
        $this->testRegisterOrigin();
        $this->testLinkModels();
        $this->testAttachRelations();
        $this->testDeactivateOrigin();
        echo "\nAll tests passed!\n";
    }
}

// Run tests
$test = new PortalTest();
$test->runAll();
