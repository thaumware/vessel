# Stock Module - TODO

## Estado: ✅ Funcional (187 tests passing)

### Dominio ✅
- [x] Entidad `Stock`
- [x] Entidad `StockItem` (con catalogItemId opcional)
- [x] Entidad `LocationStockSettings` (configuración de capacidad)
- [x] Entidad `Movement` (movimientos con Value Objects)
- [x] Entidad `Lot` (lotes con vencimiento)
- [x] Entidad `Batch` (legacy, disponible)
- [x] Value Object `CapacityValidationResult`
- [x] Value Object `ValidationResult` (genérico)
- [x] Value Object `MovementType` (enum: 15+ tipos)
- [x] Value Object `MovementStatus` (enum: pending, completed, cancelled, failed)
- [x] Service `StockCapacityService`
- [x] Service `StockMovementService` (procesa movimientos)
- [x] Service `ProcessMovementResult`
- [x] Interface `StockRepositoryInterface`
- [x] Interface `StockItemRepositoryInterface`
- [x] Interface `MovementRepositoryInterface`
- [x] Interface `LotRepositoryInterface`
- [x] Interface `LocationStockSettingsRepositoryInterface`
- [x] Interface `LocationGatewayInterface`
- [x] Interface `CatalogGatewayInterface`

### Application (Use Cases) ✅
- [x] `ManageLocationSettingsUseCase` - CRUD settings de ubicación
- [x] `GetLocationSettingsUseCase` - Consultar capacidad
- [x] `CreateStockItemUseCase`
- [x] `AdjustStockQuantity`
- [x] `ReserveStock`
- [x] `ReleaseStock`
- [x] `ApplyMovement`
- [x] `MovementFactory` - Factory en Application layer

### Infrastructure ✅
- [x] `StockServiceProvider` (bindings)
- [x] `LocationStockSettingsRepository` (Eloquent)
- [x] `StockLocationSettingsModel`
- [x] `LocationsModuleGateway` (adapter Stock→Locations)
- [x] `PortalCatalogGateway`
- [x] `InMemoryStockItemRepository`
- [x] `InMemoryMovementRepository`
- [x] `InMemoryLotRepository`
- [x] `InMemoryBatchRepository`
- [x] Migración `stock_location_settings`
- [x] Migración `stock_lots`
- [x] Migración `stock_movements` (update)

### HTTP/Controllers ✅
- [x] `MovementController` - API de movimientos
- [x] `CapacityController` - API de capacidad
- [x] `StockItemController` - CRUD items
- [x] Rutas CRUD stock items
- [x] Rutas para movimientos
- [x] Rutas para location settings/capacity

### Tests ✅ (187 tests, 663 assertions)
- [x] `LocationStockSettingsTest`
- [x] `CapacityValidationResultTest`
- [x] `StockCapacityServiceTest`
- [x] `ManageLocationSettingsUseCaseTest`
- [x] `InMemoryStockItemRepositoryTest`
- [x] `InMemoryMovementRepositoryTest`
- [x] `InMemoryBatchRepositoryTest`
- [x] `MovementTest` (entidad)
- [x] `MovementTypeTest` (value object)
- [x] `LotTest` (entidad)
- [x] `StockMovementServiceTest` (14 tests)
- [x] `StockItemUseCasesTest`
- [x] `MovementFactoryTest`

### Documentación ✅
- [x] Docusaurus: `docs/modules/stock.mdx`
- [x] Arquitectura hexagonal documentada
- [x] API playground interactivo
- [x] Ejemplos curl

---

## Pendiente (Nice to have)

### Infrastructure
- [ ] `EloquentMovementRepository`
- [ ] `EloquentLotRepository`
- [ ] Modelo `StockMovement` (Eloquent)
- [ ] Modelo `StockLot` (Eloquent)

### Features
- [ ] Eventos de dominio (StockAdjusted, CapacityExceeded, LotExpiring)
- [ ] Soporte FIFO/FEFO en movimientos de salida
- [ ] Jobs de verificación de vencimientos
- [ ] Reportes de stock por ubicación/lote
- [ ] Tests de integración con BD
- [ ] Tests Feature de API
