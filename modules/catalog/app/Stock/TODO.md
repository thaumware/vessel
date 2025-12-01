# Stock Module - TODO

## Estado: En desarrollo activo

### Dominio
- [x] Entidad `Stock`
- [x] Entidad `StockItem` (con catalogItemId opcional)
- [x] Entidad `LocationStockSettings` (configuración de capacidad)
- [x] Entidad `Movement` (movimientos con Value Objects)
- [x] Entidad `Lot` (lotes con vencimiento)
- [x] Value Object `CapacityValidationResult`
- [x] Value Object `MovementType` (enum: receipt, shipment, reserve, release, etc.)
- [x] Value Object `MovementStatus` (enum: pending, completed, cancelled, failed)
- [x] Service `StockCapacityService`
- [x] Service `StockMovementService` (procesa movimientos)
- [x] Interface `StockRepositoryInterface`
- [x] Interface `StockItemRepositoryInterface`
- [x] Interface `MovementRepositoryInterface`
- [x] Interface `LotRepositoryInterface`
- [x] Interface `LocationStockSettingsRepositoryInterface`
- [x] Interface `LocationGatewayInterface` (para consultar jerarquía)
- [x] Interface `CatalogGatewayInterface`
- [ ] Entidad `Batch` (legacy, reemplazado por Lot)

### Application (Use Cases)
- [x] `ManageLocationSettingsUseCase` - CRUD settings de ubicación
- [x] `GetLocationSettingsUseCase` - Consultar capacidad
- [x] `CreateStockItemUseCase`
- [x] `AdjustStockQuantity`
- [x] `ReserveStock`
- [x] `ReleaseStock`
- [ ] `TransferStockUseCase` - Transferencia entre ubicaciones
- [ ] `ProcessMovementUseCase` - Procesar cualquier tipo de movimiento

### Infrastructure
- [x] `LocationStockSettingsRepository` (Eloquent)
- [x] `StockLocationSettingsModel`
- [x] `LocationsModuleGateway` (adapter Stock→Locations)
- [x] `PortalCatalogGateway`
- [x] `InMemoryStockItemRepository`
- [x] `InMemoryMovementRepository`
- [x] `InMemoryLotRepository`
- [x] Migración `stock_location_settings`
- [x] Migración `stock_lots`
- [x] Migración update `stock_movements` (nuevas columnas)
- [ ] `EloquentMovementRepository`
- [ ] `EloquentLotRepository`
- [ ] Modelo `StockMovement`
- [ ] Modelo `StockLot`

### HTTP/Controllers
- [x] `MovementController` - API de movimientos
- [x] `CapacityController` - API de capacidad
- [x] Rutas CRUD stock items
- [x] Rutas para movimientos (receipt, shipment, reserve, release, adjustment, transfer)
- [x] Rutas para location settings/capacity

### Tests
- [x] `LocationStockSettingsTest` (entidad)
- [x] `CapacityValidationResultTest` (value object)
- [x] `StockCapacityServiceTest` (servicio con mocks)
- [x] `ManageLocationSettingsUseCaseTest`
- [x] `InMemoryStockItemRepositoryTest`
- [x] `InMemoryMovementRepositoryTest`
- [x] `MovementTest` (entidad)
- [x] `MovementTypeTest` (value object)
- [x] `LotTest` (entidad)
- [x] `StockMovementServiceTest` (14 tests)
- [ ] Tests de integración con BD
- [ ] Tests Feature de API

### Pendiente
- [ ] Validación de capacidad integrada en movimientos
- [ ] Eventos de dominio (StockAdjusted, CapacityExceeded, LotExpiring)
- [ ] Soporte FIFO/FEFO en movimientos de salida
- [ ] Service Provider para bindings
- [ ] Jobs de verificación de vencimientos
- [ ] Reportes de stock por ubicación/lote
