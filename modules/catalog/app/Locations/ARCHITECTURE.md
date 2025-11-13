# Arquitectura del Módulo Locations

## Visión General

El módulo **Locations** implementa una arquitectura hexagonal completa para la gestión de locaciones físicas en el sistema de inventario. Soporta múltiples adaptadores de persistencia (Eloquent/SQL y In-Memory) que se seleccionan dinámicamente mediante headers HTTP.

## Arquitectura Hexagonal

```
           ┌─────────────────────────────────────┐
           │           APPLICATION LAYER          │
           │                                     │
           │  ┌─────────────────────────────────┐ │
           │  │        USE CASES                │ │
           │  │  - CreateLocation               │ │
           │  │  - ListLocations                │ │
           │  │  - GetLocation                  │ │
           │  │  - UpdateLocation               │ │
           │  │  - DeleteLocation               │ │
           │  └─────────────────────────────────┘ │
           └─────────────────────────────────────┘
                           │
                           │ (interfaces)
                           │
           ┌─────────────────────────────────────┐
           │          DOMAIN LAYER               │
           │                                     │
           │  ┌─────────────────────────────────┐ │
           │  │        ENTITIES                 │ │
           │  │  - Location                     │ │
           │  │  - Address                      │ │
           │  │  - City                         │ │
           │  │  - Warehouse                    │ │
           │  └─────────────────────────────────┘ │
           │                                     │
           │  ┌─────────────────────────────────┐ │
           │  │      INTERFACES                 │ │
           │  │  - LocationRepository           │ │
           │  └─────────────────────────────────┘ │
           └─────────────────────────────────────┘
                           │
                           │ (implementations)
                           │
           ┌─────────────────────────────────────┐
           │       INFRASTRUCTURE LAYER          │
           │                                     │
           │  ┌─────────────────────────────────┐ │
           │  │     IN (HTTP)                   │ │
           │  │  - LocationsController          │ │
           │  │  - LocationsRoutes              │ │
           │  │  - AdapterMiddleware            │ │
           │  └─────────────────────────────────┘ │
           │                                     │
           │  ┌─────────────────────────────────┐ │
           │  │     OUT (PERSISTENCE)           │ │
           │  │  - EloquentLocationRepository   │ │
           │  │  - InMemoryLocationRepository   │ │
           │  │  - LocationModel (Eloquent)     │ │
           │  └─────────────────────────────────┘ │
           └─────────────────────────────────────┘
```

## Estructura de Archivos

```
app/Locations/
├── README.md                           # Documentación general del módulo
├── Domain/                             # 📦 Capa de Dominio (pura)
│   ├── Entities/                       # Entidades del negocio
│   │   ├── Location.php                # Locación física
│   │   ├── Address.php                 # Dirección
│   │   ├── City.php                    # Ciudad
│   │   ├── Warehouse.php               # Almacén
│   │   └── README.md                   # Documentación de entidades
│   └── Interfaces/                     # Puertos/Contratos
│       └── LocationRepository.php      # Contrato para repositorios
├── Application/                        # 🚀 Capa de Aplicación
│   └── UseCases/                       # Casos de uso
│       ├── CreateLocation.php          # Crear locación
│       ├── ListLocations.php           # Listar locaciones
│       ├── GetLocation.php             # Obtener locación por ID
│       ├── UpdateLocation.php          # Actualizar locación
│       └── DeleteLocation.php          # Eliminar locación
└── Infrastructure/                     # 🔧 Capa de Infraestructura
    ├── LocationsServiceProvider.php    # Proveedor de servicios
    ├── In/                             # 👥 Adaptadores de Entrada
    │   └── Http/
    │       ├── Controllers/
    │       │   └── LocationsController.php
    │       ├── Middleware/
    │       │   └── AdapterMiddleware.php    # Selector dinámico de adapters
    │       └── Routes/
    │           └── LocationsRoutes.php
    └── Out/                            # 💾 Adaptadores de Salida
        ├── Data/
        │   └── locations.php           # Datos de ejemplo para In-Memory
        ├── Database/
        │   └── Migrations/
        │       ├── 0001_Locations.php
        │       └── 0002_Remove_Foreign_Key_From_Locations_Locations.php
        ├── InMemory/
        │   └── InMemoryLocationRepository.php
        └── Models/
            ├── ArrayLocationRepository.php
            └── Eloquent/
                ├── AddressModel.php
                ├── CityModel.php
                ├── LocationModel.php
                ├── WarehouseModel.php
                └── EloquentLocationRepository.php
```

## Adaptadores de Persistencia

### 🔄 Adaptador Dinámico
- **Middleware**: `AdapterMiddleware` detecta el header `X-LOCATION-ADAPTER`
- **SQL** (por defecto): Usa Eloquent ORM con MySQL/PostgreSQL
- **Local** (In-Memory): Usa arrays en memoria RAM

### 📊 Esquema de Base de Datos

```sql
-- Direcciones
CREATE TABLE locations_addresses (
    id UUID PRIMARY KEY,
    name VARCHAR(255),
    address_type VARCHAR(255),
    description TEXT NULL,
    workspace_id UUID NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Locaciones
CREATE TABLE locations_locations (
    id UUID PRIMARY KEY,
    name VARCHAR(255),
    description TEXT NULL,
    type VARCHAR(255), -- warehouse, store, office, distribution_center
    address_id UUID NULL, -- Sin foreign key constraint
    workspace_id UUID NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);
```

## API Endpoints

| Método | Endpoint | Descripción | Adaptador |
|--------|----------|-------------|-----------|
| GET | `/api/v1/locations/read` | Listar todas las locaciones | SQL/Local |
| GET | `/api/v1/locations/show/{id}` | Obtener locación por ID | SQL/Local |
| POST | `/api/v1/locations/create` | Crear nueva locación | SQL/Local |
| PUT | `/api/v1/locations/update/{id}` | Actualizar locación | SQL/Local |
| DELETE | `/api/v1/locations/delete/{id}` | Eliminar locación | SQL/Local |

### Headers para Selección de Adaptador

```bash
# Usar adaptador SQL (Eloquent)
GET /api/v1/locations/read

# Usar adaptador Local (In-Memory)
GET /api/v1/locations/read
X-LOCATION-ADAPTER: local
```

## Flujo de una Request

1. **HTTP Request** → `LocationsRoutes.php`
2. **Middleware** → `AdapterMiddleware` selecciona repositorio
3. **Controller** → `LocationsController` valida y ejecuta UseCase
4. **UseCase** → Lógica de negocio pura
5. **Repository** → Persistencia (SQL o In-Memory)
6. **Response** → JSON de vuelta al cliente

## Beneficios de la Arquitectura

- ✅ **Dominio Puro**: Lógica de negocio independiente de frameworks
- ✅ **Adaptadores Intercambiables**: Cambiar persistencia sin tocar el dominio
- ✅ **Testabilidad**: UseCases se pueden testear sin infraestructura
- ✅ **Mantenibilidad**: Separación clara de responsabilidades
- ✅ **Escalabilidad**: Fácil agregar nuevos adaptadores

## Notas de Implementación

- **IDs Automáticos**: Las entidades generan UUID automáticamente
- **Validación**: Se hace en el controlador antes de ejecutar UseCases
- **Inmutabilidad**: Las entidades siguen el patrón immutable en updates
- **Soft Deletes**: Soporte para auditoría y recuperación
- **Workspace**: Preparado para multi-tenancy por workspace