# Guía de Arquitectura y Patrones - Catálogo Module

## Visión General

El módulo de catálogo sigue una **arquitectura hexagonal (ports & adapters)** con el objetivo de mantener el dominio puro y los adaptadores de infraestructura intercambiables.

## Estructura por Módulo

Cada módulo del catálogo sigue esta estructura consistente:

```
app/{ModuleName}/
├── README.md                           # Documentación del módulo
├── Domain/                             # Lógica de negocio pura
│   ├── Entities/                       # Entidades del dominio
│   │   ├── Entity.php                  # Clases de entidad
│   │   └── README.md                   # Documentación de entidades
│   ├── Interfaces/                     # Puertos (interfaces)
│   │   └── RepositoryInterface.php     # Contratos para adapters
│   └── DTOs/                           # (opcional) Data Transfer Objects
├── Application/                        # Casos de uso
│   └── UseCases/
│       └── UseCase.php                 # Lógica de aplicación
└── Infrastructure/                     # Adaptadores externos
    ├── {ModuleName}ServiceProvider.php # Configuración del módulo
    ├── In/                             # Adaptadores de entrada
    │   └── Http/
    │       ├── Controllers/
    │       │   └── Controller.php      # Controladores HTTP
    │       └── Routes/
    │           └── Routes.php          # Definición de rutas
    └── Out/                            # Adaptadores de salida
        ├── Data/                       # Archivos de datos (temporal)
        │   └── data.php                # Arrays PHP para prototipado
        └── Models/                     # Repositorios y modelos
            ├── ArrayRepository.php     # Implementación array-backed
            └── EloquentModel.php       # (futuro) Implementación SQL
```

## Patrones y Convenciones

### 1. Arquitectura Hexagonal
- **Dominio**: Libre de dependencias externas, contiene lógica de negocio pura
- **Aplicación**: Coordina casos de uso, orquesta entidades del dominio
- **Infraestructura**: Adaptadores intercambiables (HTTP, SQL, archivos, etc.)

### 2. Inyección de Dependencias
- Los UseCases reciben dependencias a través del constructor
- Los ServiceProviders registran bindings en el contenedor de Laravel
- Auto-discovery automático de ServiceProviders

### 3. Persistencia Intercambiable
- **Fase actual**: Eloquent repositories con base de datos relacional
- **Fase anterior**: Array-backed repositories para prototipado rápido
- **Fase futura**: MongoDB, Redis, o cualquier otro storage
- Los archivos de datos están en `Infrastructure/Out/Data/` (solo para prototipado)

### 4. APIs RESTful
- Prefijo: `/api/v1/{module}/`
- Endpoints CRUD estándar: `create`, `read`, `show/{id}`, `update/{id}`, `delete/{id}`
- Respuestas JSON consistentes
- Validación en controladores usando Laravel Validation

### 5. Entidades
- **Inmutables**: Nuevas instancias para cambios (patrón funcional)
- **HasId trait**: Generación automática de IDs únicos
- **Getters**: Acceso a propiedades
- **toArray()**: Serialización para APIs

### 6. UseCases
- Un método `execute()` principal
- Lógica de validación de negocio
- Coordinación entre entidades y repositorios
- Retorno de resultados o excepciones

### 7. Modelos Eloquent
- Extienden `EloquentModel` (Shared adapter)
- Usan `SoftDeletes` y `HasFactory`
- Nombres de tabla: `catalog_{entity}` (ej: `catalog_locations`)
- UUID como primary keys
- Campos `workspace_id` para multi-tenancy
- Timestamps automáticos (`created_at`, `updated_at`, `deleted_at`)

### 8. Migrations y Seeders
- Migrations en `Infrastructure/Out/Database/Migrations/`
- Seeders en `Infrastructure/Out/Database/Seeders/`
- Nombres: `{Module}{Entity}Seeder.php`
- Carga automática por ServiceProvider

## Ciclo de Desarrollo

1. **Diseñar Dominio**: Crear entidades e interfaces
2. **Implementar UseCases**: Lógica de aplicación
3. **Crear Adaptadores**: Controladores, rutas, repositorios
4. **Configurar ServiceProvider**: Bindings y rutas
5. **Documentar**: README.md y docs en Docusaurus
6. **Probar**: APIs funcionales con datos de ejemplo

## Migración de Array a SQL

Cuando sea necesario migrar a base de datos:

1. Crear migrations: `Infrastructure/Out/Database/Migrations/`
2. Implementar modelos Eloquent: `Infrastructure/Out/Models/Eloquent*.php`
3. Actualizar ServiceProvider: Cambiar bindings
4. **Sin cambios** en Domain ni UseCases

## Documentación

- **README.md por módulo**: Estructura, utilidad, ejemplos
- **Docusaurus**: Documentación interactiva con ApiPlayground
- **ARCHITECTURE.md**: Mapa árbol actualizado del proyecto

## Módulos Existentes

- **UoM**: Unidades de medida con conversiones
- **Taxonomy**: Vocabularios y términos jerárquicos
- **Locations**: Locaciones físicas agrupadas por direcciones

## Herramientas de Desarrollo

- **Laravel Artisan**: `php artisan route:list` para verificar rutas
- **PHP linter**: `php -l archivo.php` para sintaxis
- **Docusaurus**: `npm run start` para documentación local