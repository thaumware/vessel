# ARCHITECTURE — mapa árbol (catalog module)

Mapa explícito en forma de árbol (para agentes):

```
catalog/
├─ app/
│  ├─ AppServiceProvider.php
│  ├─ Items/
│  │  └─ ItemsServiceProvider.php
  ├─ Locations/
  │  ├─ ARCHITECTURE.md                 # 🏗️ Arquitectura detallada del módulo
  │  ├─ README.md
  │  ├─ Domain/
  │  │  ├─ Entities/
  │  │  │  ├─ Location.php
  │  │  │  ├─ Address.php
  │  │  │  ├─ City.php
  │  │  │  ├─ Warehouse.php
  │  │  │  └─ README.md
  │  │  └─ Interfaces/
  │  │     └─ LocationRepository.php
  │  ├─ Application/
  │  │  └─ UseCases/
  │  │     ├─ CreateLocation.php
  │  │     ├─ ListLocations.php
  │  │     ├─ GetLocation.php
  │  │     ├─ UpdateLocation.php
  │  │     └─ DeleteLocation.php
  │  └─ Infrastructure/
  │     ├─ LocationsServiceProvider.php
  │     ├─ In/Http/
  │     │  ├─ Controllers/
  │     │  │  └─ LocationsController.php
  │     │  ├─ Middleware/
  │     │  │  └─ AdapterMiddleware.php
  │     │  └─ Routes/
  │     │     └─ LocationsRoutes.php
  │     └─ Out/
  │        ├─ Data/
  │        │  └─ locations.php
  │        ├─ Database/
  │        │  └─ Migrations/
  │        │     ├─ 0001_Locations.php
  │        │     └─ 0002_Remove_Foreign_Key_From_Locations_Locations.php
  │        ├─ InMemory/
  │        │  └─ InMemoryLocationRepository.php
  │        └─ Models/
  │           ├─ ArrayLocationRepository.php
  │           └─ Eloquent/
  │              ├─ CityModel.php
  │              ├─ AddressModel.php
  │              ├─ LocationModel.php
  │              ├─ WarehouseModel.php
  │              └─ EloquentLocationRepository.php
│  ├─ Notifications/
│  ├─ Stock/
│  ├─ Taxonomy/
│  │  ├─ Domain/
│  │  │  ├─ Entities/
│  │  │  │  ├─ Vocabulary.php
│  │  │  │  ├─ Term.php
│  │  │  │  └─ TermRelation.php
│  │  │  ├─ DTOs/
│  │  │  │  └─ TermTreeNode.php
│  │  │  └─ Interfaces/
│  │  └─ Infrastructure/
│  │     ├─ In/Http/
│  │     │  ├─ Controllers/TaxonomyController.php
│  │     │  └─ Routes/TaxonomyRoutes.php
│  │     └─ Out/Models/Eloquent/VocabularyModel.php
│  └─ Uom/
│     ├─ Domain/
│     │  ├─ Entities/
│     │  │  ├─ Measure.php
│     │  │  └─ Conversion.php
│     │  └─ Interfaces/
│     │     └─ MeasureRepository.php
│     └─ Infrastructure/
│        ├─ In/Http/
│        │  ├─ UomController.php
│        │  └─ UomRoutes.php
│        └─ Out/
│           ├─ Data/measures.php
│           ├─ Data/conversions.php
│           └─ Models/MeasureRepository.php
├─ bootstrap/
│  ├─ app.php
│  └─ providers.php
├─ config/
└─ apps/
   └─ vessel-docs/
      └─ docs/modules/
         └─ uom.mdx
```

Puntos directos para integración automatizada:
- Service discovery: `app/AppServiceProvider.php` (auto-registra `app/*/*ServiceProvider.php`).
- Rutas por módulo: `app/*/Infrastructure/In/Http/*Routes.php` (cargadas por ServiceProvider).
- Migrations por módulo: `app/*/Infrastructure/Out/Database/Migrations` (cargadas por ServiceProvider).

Dónde buscar por tipo de artefacto:
- Contracts/interfaces (Domain): `app/*/Domain/Interfaces`
- UseCases/Logica: `app/*/Domain/UseCases`
- HTTP entrypoints: `app/*/Infrastructure/In/Http`
- Persistencia/adapters: `app/*/Infrastructure/Out`
- Module wiring: `app/*/Infrastructure/*ServiceProvider.php`

Mantén el árbol actualizado cuando agregues o muevas providers o módulos.

---

## 📚 Arquitecturas Específicas por Módulo

Cada módulo puede tener su propia documentación de arquitectura detallada:

- **Locations**: [`app/Locations/ARCHITECTURE.md`](app/Locations/ARCHITECTURE.md) - Arquitectura hexagonal completa con adaptadores dinámicos
- **Taxonomy**: Próximamente
- **Uom**: Próximamente
- **Items**: Próximamente