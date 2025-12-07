# AdapterMiddleware - Middleware Genérico para Switching de Adaptadores

Este middleware permite cambiar dinámicamente entre diferentes implementaciones de repositorios (adaptadores) basándose en headers HTTP.

## Funcionamiento

El middleware lee un header específico para cada módulo y cambia el binding del contenedor de dependencias de Laravel en tiempo de ejecución.

## Configuración por Módulo

### Locations
- **Header**: `X-LOCATION-ADAPTER`
- **Valores**:
  - `local` → Usa `InMemoryLocationRepository`
  - Cualquier otro valor → Usa `EloquentLocationRepository`

### Taxonomy
- **Header**: `X-TAXONOMY-ADAPTER`
- **Valores**:
  - `local` → Usa repositorios InMemory (pendiente de implementar)
  - Cualquier otro valor → Usa repositorios Eloquent

## Uso

### Locations
```bash
# Usar adaptador SQL (por defecto)
GET /api/v1/locations/read

# Usar adaptador InMemory
GET /api/v1/locations/read
Header: X-LOCATION-ADAPTER: local
```

### Taxonomy
```bash
# Usar adaptador SQL (por defecto)
GET /api/v1/taxonomy/vocabularies/read

# Usar adaptador InMemory (cuando esté implementado)
GET /api/v1/taxonomy/vocabularies/read
Header: X-TAXONOMY-ADAPTER: local
```

## Agregar Nuevo Módulo

Para agregar un nuevo módulo al middleware:

1. Agregar configuración en `AdapterMiddleware::$moduleConfigs`
2. Registrar el middleware en el ServiceProvider del módulo
3. Aplicar el middleware a las rutas del módulo

Ejemplo:
```php
// En AdapterMiddleware.php
'taxonomy' => [
    'interfaces' => [
        TermRepositoryInterface::class,
        VocabularyRepositoryInterface::class,
    ],
    'eloquent' => [
        TermRepositoryInterface::class => TermRepository::class,
        VocabularyRepositoryInterface::class => VocabularyRepository::class,
    ],
    'inmemory' => [
        // Implementaciones InMemory cuando estén disponibles
    ],
],

// En TaxonomyServiceProvider.php
$this->app['router']->aliasMiddleware('taxonomy_adapter', AdapterMiddleware::class . ':taxonomy');

// En TaxonomyRoutes.php
Route::prefix('api/v1/taxonomy')->middleware('taxonomy_adapter')->group(function () {
    // rutas...
});
```

## Arquitectura

Este middleware sigue el patrón de **Dependency Injection Container Binding** para cambiar implementaciones en tiempo de ejecución sin modificar el código de la aplicación.