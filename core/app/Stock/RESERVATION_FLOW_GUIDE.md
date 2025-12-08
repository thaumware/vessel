# Flujo de Reservaciones - API Simplificada

## 🎯 Problema Resuelto

**Requisito**: Endpoints simples para reservar/liberar stock que combinen información de Stock + Locations + Catalog sin romper modularidad.

**Solución**: Use cases que orquestan múltiples módulos + Controller con 3 endpoints dedicados.

---

## 📦 Endpoints

### 1. Validar Reserva (Sin modificar estado)

```bash
POST /api/v1/stock/reservations/validate
Content-Type: application/json

{
  "item_id": "ITEM-001",
  "location_id": "WAREHOUSE-MAIN",
  "quantity": 30
}
```

**Response 200 OK**:
```json
{
  "can_reserve": true,
  "available_quantity": 80,
  "reserved_quantity": 20,
  "total_quantity": 100,
  "max_reservation_allowed": 80,
  "errors": [],
  "warnings": ["La reserva dejará stock disponible negativo"],
  "item_info": {
    "id": "ITEM-001",
    "name": "Producto XYZ",
    "sku": "SKU-001"
  },
  "location_info": {
    "id": "WAREHOUSE-MAIN",
    "name": "Almacén Principal"
  }
}
```

**Response 422 Unprocessable** (si no puede reservar):
```json
{
  "can_reserve": false,
  "available_quantity": 10,
  "reserved_quantity": 20,
  "total_quantity": 30,
  "max_reservation_allowed": null,
  "errors": [
    "Stock disponible insuficiente. Disponible: 10, solicitado: 30"
  ],
  "warnings": [],
  "item_info": {...},
  "location_info": {...}
}
```

---

### 2. Crear Reserva

```bash
POST /api/v1/stock/reservations/reserve
Content-Type: application/json

{
  "item_id": "ITEM-001",
  "location_id": "WAREHOUSE-MAIN",
  "quantity": 30,
  "reference_type": "sales_order",
  "reference_id": "SO-2024-001",
  "reason": "Reserva para orden de venta #001",
  "performed_by": "user@example.com",
  "skip_validation": false
}
```

**Response 201 Created**:
```json
{
  "success": true,
  "reservation_id": "mov-abc-123-xyz",
  "new_reserved_quantity": 50,
  "new_available_quantity": 50,
  "errors": [],
  "movement": {
    "id": "mov-abc-123-xyz",
    "type": "reserve",
    "item_id": "ITEM-001",
    "location_id": "WAREHOUSE-MAIN",
    "quantity": 30,
    "status": "completed",
    "reference_type": "sales_order",
    "reference_id": "SO-2024-001",
    "created_at": "2024-12-08T10:30:00Z",
    "processed_at": "2024-12-08T10:30:01Z"
  }
}
```

**Response 422 Unprocessable** (si falla validación):
```json
{
  "success": false,
  "reservation_id": null,
  "new_reserved_quantity": null,
  "new_available_quantity": null,
  "errors": [
    "Excede el límite de reserva (80% del stock total). Máximo permitido: 80, se alcanzaría: 90"
  ],
  "movement": null
}
```

---

### 3. Liberar Reserva

```bash
POST /api/v1/stock/reservations/release
Content-Type: application/json

{
  "item_id": "ITEM-001",
  "location_id": "WAREHOUSE-MAIN",
  "quantity": 30,
  "reference_id": "SO-2024-001",
  "reason": "Orden cancelada"
}
```

**Response 200 OK**:
```json
{
  "success": true,
  "new_reserved_quantity": 20,
  "new_available_quantity": 80,
  "errors": [],
  "movement": {
    "id": "mov-xyz-456-abc",
    "type": "release",
    "item_id": "ITEM-001",
    "location_id": "WAREHOUSE-MAIN",
    "quantity": 30,
    "status": "completed",
    "reference_type": "reservation_release",
    "reference_id": "SO-2024-001",
    "created_at": "2024-12-08T11:00:00Z"
  }
}
```

**Response 422 Unprocessable** (si no hay suficiente reservado):
```json
{
  "success": false,
  "new_reserved_quantity": null,
  "new_available_quantity": null,
  "errors": [
    "No hay suficiente cantidad reservada para liberar. Reservado: 20, intentando liberar: 30"
  ],
  "movement": null
}
```

---

## 🔧 Validaciones Automáticas

### En Validación (validate)
1. ✅ Item existe en catálogo
2. ✅ Locación existe
3. ✅ Hay stock en la locación
4. ✅ Stock disponible >= cantidad solicitada
5. ✅ No excede `max_reservation_percentage` (si configurado)
6. ✅ Lote no vencido (si se especifica `lot_id`)

### En Reserva (reserve)
- Todas las anteriores (si `skip_validation: false`)
- Crea Movement con `type: reserve`

### En Liberación (release)
1. ✅ Existe stock en la locación
2. ✅ Cantidad reservada >= cantidad a liberar
- Crea Movement con `type: release`

---

## 📊 Configuración de Locación

La validación respeta la configuración de cada locación:

```php
// En LocationStockSettings
[
    'location_id' => 'WAREHOUSE-MAIN',
    'allow_negative_stock' => false,  // Si es true, permite reservar más de lo disponible
    'max_reservation_percentage' => 80,  // Máximo 80% del stock total puede reservarse
]
```

---

## 🏗️ Arquitectura (Sin romper modularidad)

### Use Cases Creados

#### ValidateReservationUseCase
```php
- Combina: StockItemRepository + LocationStockSettingsRepository + CatalogGateway + LocationGateway
- NO modifica estado
- Retorna: ReservationValidationResult
```

#### CreateReservationUseCase
```php
- Usa: ValidateReservationUseCase + StockMovementService
- Crea Movement(type: RESERVE)
- Retorna: CreateReservationResult
```

#### ReleaseReservationUseCase
```php
- Usa: StockItemRepository + StockMovementService
- Crea Movement(type: RELEASE)
- Retorna: ReleaseReservationResult
```

### Separación de Responsabilidades

| Capa | Responsabilidad |
|------|-----------------|
| **Controller** | HTTP request/response, validación de input |
| **Use Case** | Orquestación de lógica de negocio, combina módulos |
| **Domain Service** | Lógica de movimientos (StockMovementService) |
| **Repository** | Persistencia |
| **Gateway** | Acceso a otros módulos (Catalog, Locations) |

**✅ Modularidad preservada**: Cada módulo sigue independiente, los Use Cases orquestan.

---

## 🧪 Tests

```bash
vendor/bin/phpunit app/Stock/Tests/Feature/ReservationFlowTest.php
```

Tests incluidos:
- ✅ `test_can_validate_reservation_before_creating`
- ✅ `test_can_create_reservation`
- ✅ `test_cannot_reserve_more_than_available`
- ✅ `test_cannot_exceed_max_reservation_percentage`
- ✅ `test_can_release_reservation`
- ✅ `test_cannot_release_more_than_reserved`
- ✅ `test_validation_includes_item_and_location_info`
- ✅ `test_complete_flow_validate_reserve_release`

---

## 💡 Flujo Recomendado

### Flujo Completo (UI)
```javascript
// 1. Validar ANTES de mostrar botón "Reservar"
const validation = await fetch('/api/v1/stock/reservations/validate', {
  method: 'POST',
  body: JSON.stringify({
    item_id: 'ITEM-001',
    location_id: 'WAREHOUSE-MAIN',
    quantity: 30
  })
});

if (validation.can_reserve) {
  // 2. Mostrar botón habilitado con info
  showReserveButton({
    available: validation.available_quantity,
    maxAllowed: validation.max_reservation_allowed,
    warnings: validation.warnings
  });
  
  // 3. Al confirmar, crear reserva
  const reservation = await fetch('/api/v1/stock/reservations/reserve', {
    method: 'POST',
    body: JSON.stringify({
      item_id: 'ITEM-001',
      location_id: 'WAREHOUSE-MAIN',
      quantity: 30,
      reference_id: orderId
    })
  });
  
  if (reservation.success) {
    showSuccess('Reservado: ' + reservation.reservation_id);
  }
} else {
  // Mostrar errores
  showErrors(validation.errors);
}
```

### Flujo Rápido (API to API)
```javascript
// Skipear validación si confías en los datos
const reservation = await fetch('/api/v1/stock/reservations/reserve', {
  method: 'POST',
  body: JSON.stringify({
    item_id: 'ITEM-001',
    location_id: 'WAREHOUSE-MAIN',
    quantity: 30,
    skip_validation: true  // ⚠️ Solo si ya validaste antes
  })
});
```

---

## 📝 Resumen

**Endpoints**:
- `POST /reservations/validate` - Valida sin modificar
- `POST /reservations/reserve` - Crea reserva
- `POST /reservations/release` - Libera reserva

**Ventajas**:
- ✅ API simple (1 endpoint por acción)
- ✅ Combina Stock + Locations + Catalog
- ✅ Modularidad preservada (use cases orquestan)
- ✅ Validación automática respeta configuración
- ✅ Información completa en respuesta
- ✅ Tests de integración incluidos

**Configuración respetada**:
- `allow_negative_stock`
- `max_reservation_percentage`

**Ready to use**: Solo falta seed de datos para tests.
