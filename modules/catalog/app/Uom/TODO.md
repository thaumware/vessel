# UoM Module - TODO

## Estado: Completo

### Dominio
- [x] Entidad `Unit`
- [x] Entidad `UnitConversion`
- [x] Value Object `ConversionPath`
- [x] Service `ConversionService` (BFS para encontrar rutas)
- [x] Interface `UnitRepositoryInterface`
- [x] Interface `UnitConversionRepositoryInterface`

### Application (Use Cases)
- [x] `CreateUnitUseCase`
- [x] `GetUnitUseCase`
- [x] `ListUnitsUseCase`
- [x] `UpdateUnitUseCase`
- [x] `DeleteUnitUseCase`
- [x] `CreateUnitConversionUseCase`
- [x] `ConvertUseCase` (conversión entre unidades)

### Infrastructure
- [x] `EloquentUnitRepository`
- [x] `EloquentUnitConversionRepository`
- [x] `InMemoryUnitRepository`
- [x] `InMemoryUnitConversionRepository`
- [x] Modelos Eloquent
- [x] Migraciones

### Tests
- [x] `UnitTest` (entidad)
- [x] `UnitConversionTest` (entidad)
- [x] `ConversionServiceTest` (BFS, rutas inversas)
- [x] `InMemoryUnitRepositoryTest`
- [x] `InMemoryUnitConversionRepositoryTest`
- [x] Tests de Use Cases

### API/HTTP
- [x] CRUD /uom/units
- [x] CRUD /uom/conversions
- [x] POST /uom/convert

### Completo
- [x] Conversiones transitivas (A→B→C)
- [x] Conversiones inversas automáticas
- [x] Validación de factores
