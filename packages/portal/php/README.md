# Portal

Cross-service data relationships. Framework agnostic.

## Install

```bash
composer require thaumware/portal
```

Laravel setup:

```php
// app/Providers/PortalServiceProvider.php
use Thaumware\Portal\Contracts\StorageAdapter;
use Thaumware\Portal\Contracts\DataFetcher;
use Thaumware\Portal\Adapters\IlluminateAdapter;

$this->app->singleton(IlluminateAdapter::class, fn() => 
    new IlluminateAdapter(DB::class, Http::class, Str::class)
);

$this->app->singleton(StorageAdapter::class, IlluminateAdapter::class);
$this->app->singleton(DataFetcher::class, IlluminateAdapter::class);
```

## Usage

```php
use Thaumware\Portal\Portal;

// Register origin
Portal::register('items', 'catalog_items', 'table');
Portal::register('inventory', 'http://inventory:9111/api/items', 'http');

// Link relation
Portal::link(
    modelId: $term->id,
    modelType: Term::class,
    originName: 'items',
    relatedId: $item->id
);

// Load relations (batch)
$terms = Term::all();
Portal::attach($terms);

// Access
$terms[0]->portal_items; // Array of related data
```

## Methods

- `Portal::register(name, source, type)` - Register origin
- `Portal::link(modelId, modelType, originName, relatedId, metadata)` - Create relation
- `Portal::attach(models)` - Load relations (batch)
- `Portal::deactivate(name)` - Soft delete origin
