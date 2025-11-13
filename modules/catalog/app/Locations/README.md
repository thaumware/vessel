# Locations Module

Módulo para gestión de locaciones físicas (locations), direcciones (addresses), ciudades (cities) y almacenes (warehouses). Permite agrupar y organizar locaciones geográficas para el sistema de inventario y logística.

## Estructura

```
app/Locations/
├── Application/
│   └── UseCases/
│       ├── CreateLocation.php
│       ├── ListLocations.php
│       ├── GetLocation.php
│       ├── UpdateLocation.php
│       └── DeleteLocation.php
├── Domain/
│   ├── Entities/
│   │   ├── Location.php      # Locación física (tiene address_id)
│   │   ├── Address.php       # Dirección completa
│   │   ├── City.php          # Ciudad
│   │   └── Warehouse.php     # Almacén (extiende Location)
│   └── Interfaces/
│       └── LocationRepository.php
└── Infrastructure/
    ├── In/Http/
    │   ├── Controllers/LocationsController.php
    │   └── Routes/LocationsRoutes.php
    ├── Out/
    │   ├── Data/locations.php    # Array de datos (temporal)
    │   └── Models/LocationRepository.php
    └── LocationsServiceProvider.php
```

## Utilidad

- **Gestión de locaciones**: Crear y organizar locaciones físicas con direcciones completas.
- **Agrupación geográfica**: Relacionar locations con addresses, cities y warehouses.
- **Integración con inventario**: Las locations sirven como puntos de almacenamiento para items.
- **API REST**: Endpoints para CRUD completo de locations.
- **Hexagonal architecture**: Domain puro, adapters intercambiables (file-backed → SQL).

## API Endpoints

### Crear Location
```
POST /api/v1/locations/create
Content-Type: application/json

{
  "name": "Almacén Central",
  "address_id": "addr-123",
  "description": "Almacén principal de la empresa"
}
```

### Listar Locations
```
GET /api/v1/locations/read
```

### Obtener Location
```
GET /api/v1/locations/show/{id}
```

### Actualizar Location
```
PUT /api/v1/locations/update/{id}
Content-Type: application/json

{
  "name": "Almacén Central Actualizado",
  "description": "Descripción actualizada"
}
```

### Eliminar Location
```
DELETE /api/v1/locations/delete/{id}
```

## Ejemplos de Uso

### Crear una location con address
```php
// 1. Crear address primero (asumiendo API de addresses)
$address = [
    'street' => 'Calle Principal 123',
    'city' => 'Madrid',
    'country' => 'Spain'
];

// 2. Crear location
$location = [
    'name' => 'Oficina Central',
    'address_id' => $address['id'],
    'description' => 'Sede principal'
];
```

### Integración con Warehouse
```php
// Warehouse extiende Location
$warehouse = new Warehouse(
    id: 'wh-001',
    name: 'Almacén Norte',
    address_id: 'addr-456',
    capacity: 10000,  // propiedad específica de Warehouse
    description: 'Almacén de alta capacidad'
);
```

### Búsqueda y filtrado
```php
// Listar todas las locations
$locations = $locationRepository->findAll();

// Buscar por address
$locationsInMadrid = array_filter($locations, fn($l) => 
    $l->getAddressId() === 'addr-madrid'
);
```

## Notas Técnicas

- **Persistencia temporal**: Actualmente usa arrays PHP en `Infrastructure/Out/Data/locations.php`.
- **Para producción**: Reemplazar `LocationRepository` por implementación Eloquent.
- **Validación**: Los UseCases validan reglas de negocio (ej: address_id debe existir).
- **Relaciones**: Location tiene address_id, pero no carga automáticamente (lazy loading en adapters).
- **Extensibilidad**: Warehouse es ejemplo de entidad que extiende Location.

## Próximos pasos

1. Implementar API de Addresses
2. Añadir validación de coordenadas GPS
3. Integrar con módulo de Items (locaciones de inventario)
4. Migrar a base de datos SQL