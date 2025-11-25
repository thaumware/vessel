# ARCHITECTURE — mapa árbol (catalog module)

Mapa explícito en forma de árbol (para agentes):

```
catalog/
├─ app/
│  ├─ AppServiceProvider.php              # Auto-registra módulos
│  ├─ Shared/
│  │  ├─ Domain/
│  │  │  ├─ Traits/
│  │  │  │  └─ HasId.php                  # 🔑 Trait para IDs (solo getter/setter)
│  │  │  ├─ DTOs/
│  │  │  │  ├─ PaginationParams.php
│  │  │  │  └─ PaginatedResult.php
│  │  │  └─ Interfaces/
│  │  ├─ Infrastructure/
│  │  │  └─ Middleware/
│  │  │     └─ AdapterMiddleware.php      # 🧩 Switching dinámico de repositorios via header
│  │  └─ Providers/
│  │     └─ PortalServiceProvider.php
│  │
│  ├─ Items/
│  │  ├─ Domain/
│  │  │  └─ Interfaces/
│  │  │     └─ ItemRepositoryInterface.php
│  │  └─ Infrastructure/
│  │     ├─ ItemsServiceProvider.php
│  │     └─ Out/Models/
│  │        └─ EloquentItemRepository.php
│  │
│  ├─ Locations/
│  │  ├─ ARCHITECTURE.md
│  │  ├─ Domain/
│  │  │  ├─ Entities/
│  │  │  │  ├─ Location.php               # 🏢 Entidad con ID obligatorio
│  │  │  │  ├─ Address.php
│  │  │  │  └─ ...
│  │  │  ├─ ValueObjects/
│  │  │  │  └─ LocationType.php
│  │  │  └─ Interfaces/
│  │  │     ├─ LocationRepository.php
│  │  │     └─ AddressRepository.php
│  │  ├─ Application/
│  │  │  ├─ UseCases/
│  │  │  │  ├─ CreateLocation.php         # execute(string $id, array $data)
│  │  │  │  ├─ ListLocations.php
│  │  │  │  ├─ GetLocation.php
│  │  │  │  ├─ UpdateLocation.php
│  │  │  │  └─ DeleteLocation.php
│  │  │  └─ Dtos/
│  │  │     └─ CreateLocationRequest.php
│  │  └─ Infrastructure/
│  │     ├─ LocationsServiceProvider.php
│  │     ├─ In/Http/
│  │     │  ├─ Controllers/
│  │     │  │  ├─ LocationController.php   # Genera UUID con Thaumware\Support\Uuid
│  │     │  │  └─ LocationsController.php
│  │     │  └─ Routes/
│  │     │     └─ LocationsRoutes.php
│  │     └─ Out/
│  │        ├─ Data/locations.php
│  │        ├─ Database/Migrations/
│  │        ├─ InMemory/
│  │        │  └─ InMemoryLocationRepository.php
│  │        └─ Models/Eloquent/
│  │           └─ EloquentLocationRepository.php
│  │
│  ├─ Stock/                               # 📦 MÓDULO PRINCIPAL DE INVENTARIO
│  │  ├─ Domain/
│  │  │  ├─ Entities/
│  │  │  │  ├─ StockItem.php              # 📦 Item de inventario (inmutable)
│  │  │  │  ├─ Stock.php
│  │  │  │  ├─ Movement.php
│  │  │  │  ├─ Batch.php
│  │  │  │  └─ Unit.php
│  │  │  └─ Interfaces/
│  │  │     ├─ StockItemRepositoryInterface.php
│  │  │     ├─ StockRepositoryInterface.php
│  │  │     ├─ MovementRepositoryInterface.php
│  │  │     ├─ BatchRepositoryInterface.php
│  │  │     ├─ UnitRepositoryInterface.php
│  │  │     └─ CatalogGatewayInterface.php # 🔗 Contrato para integración catálogo
│  │  ├─ Application/
│  │  │  └─ UseCases/
│  │  │     ├─ CreateStockItem.php        # Requiere $data['id']
│  │  │     ├─ GetStockItem.php
│  │  │     ├─ ListStockItems.php
│  │  │     ├─ UpdateStockItem.php        # Lanza RuntimeException si no existe
│  │  │     ├─ DeleteStockItem.php
│  │  │     ├─ AdjustStockQuantity.php
│  │  │     ├─ ReserveStock.php
│  │  │     ├─ ReleaseStock.php
│  │  │     ├─ CreateUnit.php
│  │  │     ├─ CreateBatch.php
│  │  │     ├─ ApplyMovement.php
│  │  │     └─ GetStockByLocation.php
│  │  ├─ Infrastructure/
│  │  │  ├─ StockServiceProvider.php      # Registra adapters.stock config
│  │  │  ├─ In/Http/
│  │  │  │  ├─ Controllers/
│  │  │  │  │  ├─ StockItemController.php  # Genera UUID con Uuid::v4()
│  │  │  │  │  ├─ StockController.php
│  │  │  │  │  ├─ BatchController.php
│  │  │  │  │  ├─ UnitController.php
│  │  │  │  │  └─ MovementWebhookController.php
│  │  │  │  ├─ Requests/
│  │  │  │  │  └─ CreateUnitRequest.php
│  │  │  │  └─ Routes/
│  │  │  │     └─ StockRoutes.php
│  │  │  └─ Out/
│  │  │     ├─ Data/stock_items.php
│  │  │     ├─ Database/Migrations/
│  │  │     ├─ Gateways/
│  │  │     │  └─ PortalCatalogGateway.php # 🔌 Implementación con Portal package
│  │  │     ├─ InMemory/
│  │  │     │  └─ InMemoryStockItemRepository.php
│  │  │     └─ Models/Eloquent/
│  │  │        ├─ StockItemRepository.php
│  │  │        ├─ StockRepository.php
│  │  │        ├─ MovementRepository.php
│  │  │        ├─ BatchRepository.php
│  │  │        └─ UnitRepository.php
│  │  └─ Tests/                            # 🧪 Tests unitarios por capa
│  │     ├─ StockTestCase.php
│  │     ├─ Domain/
│  │     │  └─ StockItemTest.php
│  │     ├─ Application/
│  │     │  └─ StockItemUseCasesTest.php
│  │     └─ Infrastructure/
│  │        └─ InMemoryStockItemRepositoryTest.php
│  │
│  ├─ Taxonomy/
│  │  ├─ Domain/
│  │  │  ├─ Entities/
│  │  │  │  ├─ Vocabulary.php
│  │  │  │  ├─ Term.php
│  │  │  │  └─ TermRelation.php           # camelCase: fromTermId, toTermId
│  │  │  ├─ DTOs/
│  │  │  │  └─ TermTreeNode.php
│  │  │  ├─ Interfaces/
│  │  │  │  ├─ TermRepositoryInterface.php
│  │  │  │  ├─ VocabularyRepositoryInterface.php
│  │  │  │  └─ TermRelationRepositoryInterface.php
│  │  │  └─ UseCases/
│  │  │     ├─ Term/
│  │  │     │  ├─ CreateTerm.php          # execute(string $id, ...)
│  │  │     │  ├─ ListTerms.php
│  │  │     │  ├─ GetTerm.php
│  │  │     │  ├─ UpdateTerm.php
│  │  │     │  ├─ DeleteTerm.php
│  │  │     │  ├─ GetTermTree.php
│  │  │     │  └─ GetTermBreadcrumb.php
│  │  │     ├─ TermRelation/
│  │  │     │  ├─ AddTermRelation.php     # execute(string $id, ...)
│  │  │     │  └─ RemoveTermRelation.php
│  │  │     └─ Vocabulary/
│  │  │        ├─ CreateVocabulary.php    # execute(string $id, ...)
│  │  │        ├─ ListVocabularies.php
│  │  │        ├─ GetVocabulary.php
│  │  │        ├─ UpdateVocabulary.php
│  │  │        └─ DeleteVocabulary.php
│  │  ├─ Infrastructure/
│  │  │  ├─ TaxonomyServiceProvider.php   # Registra adapters.taxonomy config
│  │  │  ├─ In/Http/
│  │  │  │  ├─ Controllers/
│  │  │  │  │  └─ TaxonomyController.php   # Genera UUIDs con Uuid::v4()
│  │  │  │  └─ Routes/
│  │  │  │     └─ TaxonomyRoutes.php
│  │  │  └─ Out/
│  │  │     ├─ Data/terms.php
│  │  │     ├─ InMemory/
│  │  │     │  ├─ InMemoryTermRepository.php
│  │  │     │  └─ InMemoryVocabularyRepository.php
│  │  │     └─ Models/Eloquent/
│  │  │        ├─ TermModel.php
│  │  │        ├─ TermRelationshipModel.php
│  │  │        ├─ TermRepository.php
│  │  │        ├─ VocabularyModel.php
│  │  │        └─ VocabularyRepository.php
│  │  └─ Tests/
│  │     ├─ TaxonomyTestCase.php
│  │     └─ Domain/
│  │        └─ TermTest.php
│  │
│  ├─ Uom/
│  │  ├─ Domain/
│  │  │  ├─ Entities/
│  │  │  │  ├─ Measure.php
│  │  │  │  └─ Conversion.php
│  │  │  └─ Interfaces/
│  │  │     └─ MeasureRepository.php
│  │  ├─ Application/
│  │  │  └─ UseCases/
│  │  │     ├─ ListMeasures.php
│  │  │     └─ ConvertMeasure.php
│  │  ├─ Infrastructure/
│  │  │  ├─ UomServiceProvider.php
│  │  │  ├─ In/Http/
│  │  │  │  ├─ UomController.php
│  │  │  │  └─ UomRoutes.php
│  │  │  └─ Out/
│  │  │     ├─ Data/
│  │  │     │  ├─ measures.php
│  │  │     │  └─ conversions.php
│  │  │     └─ Models/
│  │  │        ├─ ArrayMeasureRepository.php
│  │  │        └─ MeasureRepository.php
│  │  └─ Tests/
│  │     ├─ UomTestCase.php
│  │     └─ Domain/
│  │        └─ MeasureTest.php
│  │
│  └─ Pricing/
│     └─ Infrastructure/
│        └─ PricingServiceProvider.php
│
├─ bootstrap/
│  ├─ app.php
│  └─ providers.php
├─ config/
├─ tests/                                  # Tests Feature/Integration globales
│  ├─ TestCase.php
│  ├─ Feature/
│  └─ Integration/
└─ phpunit.xml                             # Testsuites por módulo
```

---

## 🔑 Convenciones de Arquitectura

### Generación de IDs

-   **Domain**: Entidades reciben `string $id` obligatorio en constructor
-   **Application**: UseCases reciben ID como parámetro obligatorio
-   **Infrastructure**: Controllers generan UUID con `Thaumware\Support\Uuid\Uuid::v4()`

### Naming

-   **Domain/Application**: camelCase (`vocabularyId`, `fromTermId`)
-   **Infrastructure (toArray)**: snake_case (`vocabulary_id`, `from_term_id`)

### HasId Trait

```php
// Solo getter/setter, NO genera UUIDs
trait HasId {
    private string $id;
    public function getId(): string { return $this->id; }
    public function setId(string $id): void { $this->id = $id; }
}
```

---

## 🧩 Sistema de Adaptadores Dinámicos

El middleware `AdapterMiddleware` permite cambiar repositorios en runtime según header HTTP:

### Configuración en ServiceProvider

```php
// StockServiceProvider.php
$this->app->instance('adapters.stock', [
    'interfaces' => [
        StockItemRepositoryInterface::class => [
            'local' => InMemoryStockItemRepository::class,
            'eloquent' => StockItemRepository::class,
        ],
    ],
]);
```

### Uso en Rutas

```php
Route::middleware(['stock_adapter'])->group(function () {
    Route::get('/stock/items', [StockItemController::class, 'list']);
});
```

### Request con Header

```bash
# Usa Eloquent (default)
curl http://localhost/api/stock/items

# Usa InMemory
curl http://localhost/api/stock/items -H "X-STOCK-ADAPTER: local"
```

---

## 📍 Puntos de Integración

| Tipo                  | Ubicación                                              |
| --------------------- | ------------------------------------------------------ |
| Service discovery     | `app/AppServiceProvider.php`                           |
| Rutas por módulo      | `app/*/Infrastructure/In/Http/*Routes.php`             |
| Migrations            | `app/*/Infrastructure/Out/Database/Migrations`         |
| Contracts/Interfaces  | `app/*/Domain/Interfaces`                              |
| UseCases              | `app/*/Domain/UseCases` o `app/*/Application/UseCases` |
| HTTP entrypoints      | `app/*/Infrastructure/In/Http`                         |
| Persistencia          | `app/*/Infrastructure/Out`                             |
| Module wiring         | `app/*/Infrastructure/*ServiceProvider.php`            |
| Shared infrastructure | `app/Shared/Infrastructure`                            |
| Tests unitarios       | `app/*/Tests`                                          |
| Tests feature         | `tests/Feature`                                        |

---

## 📚 Documentación por Módulo

-   **Stock**: [`apps/vessel-docs/docs/modules/stock.mdx`](../../apps/vessel-docs/docs/modules/stock.mdx)
-   **Locations**: [`apps/vessel-docs/docs/modules/locations.mdx`](../../apps/vessel-docs/docs/modules/locations.mdx)
-   **Taxonomy**: [`apps/vessel-docs/docs/modules/taxonomy.mdx`](../../apps/vessel-docs/docs/modules/taxonomy.mdx)
-   **UoM**: [`apps/vessel-docs/docs/modules/uom.mdx`](../../apps/vessel-docs/docs/modules/uom.mdx)
