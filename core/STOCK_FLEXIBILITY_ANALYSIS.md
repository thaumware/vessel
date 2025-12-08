# Análisis de Flexibilidad del Sistema de Stock en Vessel

## ✅ SOLUCIÓN IMPLEMENTADA: Extensibilidad Real

**Problema original**: Enum hardcodeado = inflexible  
**Solución**: **MovementHandlerInterface + Registry** = Extensible sin tocar Domain

---

## 🎯 Resumen Ejecutivo

| Aspecto | ¿Hardcodeado? | ¿Extensible? | Cómo |
|---------|---------------|--------------|------|
| **Tipos Estándar (Enum)** | ✅ SÍ (18 tipos) | ❌ NO | Modificar enum + deploy |
| **Tipos Custom** | ❌ NO | ✅ 100% | Crear handler + registrar |
| **Meta Data (JSON)** | ❌ NO | ✅ 100% | Campo libre |
| **Reference Types** | ❌ NO | ✅ 100% | String libre |
| **Validaciones** | ❌ NO | ✅ 100% | Custom por handler |
| **Devoluciones** | ✅ Incluidas | ✅ `RETURN` type | Out of the box |

---

## 🏗️ Arquitectura de Extensibilidad

### Domain (NO tocar)
```php
enum MovementType {
    RECEIPT, RETURN, SHIPMENT, RESERVE, RELEASE, ...
    CUSTOM  // ✅ Nuevo: tipo genérico extensible
}

interface MovementHandlerInterface {
    supports(string $type): bool
    validate(Movement, StockItem): void
    handle(Movement, StockItem): StockItem
}
```

### Infrastructure (EXTENDER aquí)
```php
class MovementHandlerRegistry {
    register(MovementHandlerInterface $handler)
    findHandler(string $type): ?MovementHandlerInterface
}

// Ejemplos incluidos:
CustomerLoanHandler       // customer_loan, loan_return
ConsignmentHandler        // consignment_out, consignment_return
```

---

---

## 🚀 Cómo Agregar Tipos Custom (3 pasos)

### 1. Crear Handler
```php
namespace App\Stock\Infrastructure\Handlers;

class RepairInHandler implements MovementHandlerInterface
{
    public function supports(string $movementType): bool
    {
        return $movementType === 'repair_in';
    }

    public function validate(Movement $movement, StockItem $stockItem): void
    {
        if (!isset($movement->getMeta()['repair_order_id'])) {
            throw new \DomainException('repair_order_id requerido');
        }
    }

    public function handle(Movement $movement, StockItem $stockItem): StockItem
    {
        // ✅ IMPORTANTE: Retornar nuevo StockItem (inmutable)
        return $stockItem->adjustQuantity($movement->getQuantity());
    }

    public function describe(): string
    {
        return 'Repair In Handler: Entrada desde reparación';
    }
}
```

### 2. Registrar en ServiceProvider
```php
// StockServiceProvider.php
$registry = new MovementHandlerRegistry();
$registry->register(new RepairInHandler()); // ✅
```

### 3. Usar desde API
```bash
POST /api/v1/stock/movements
{
  "type": "custom",              # MovementType::CUSTOM
  "reference_type": "repair_in", # Identifica el handler
  "quantity": 1,
  "meta": {
    "repair_order_id": "RO-12345"
  }
}
```

---

## ✅ Tests: 7 tests passing

```bash
$ vendor/bin/phpunit app/Stock/Tests/Feature/CustomMovementHandlersTest.php
✔ Custom loan handler reduces stock
✔ Custom loan return adds stock
✔ Custom consignment out reduces stock
✔ Custom consignment return adds stock
✔ Validation fails for insufficient stock in custom handler
✔ Multiple custom handlers can coexist
✔ Standard enum types still work with custom handlers registered

OK (7 tests, 17 assertions)
```

---

## 🔒 Lo que SÍ está Hardcodeado

### 1. **Tipos de Movimiento** (Enum)

```php
enum MovementType: string
{
    case RECEIPT = 'receipt';          // ✅ Recepción
    case RETURN = 'return';            // ✅ Devolución de cliente
    case SHIPMENT = 'shipment';        // ✅ Envío
    case RESERVE = 'reserve';          // ✅ Reserva
    case RELEASE = 'release';          // ✅ Liberar reserva
    case ADJUSTMENT_IN = 'adjustment_in';
    case ADJUSTMENT_OUT = 'adjustment_out';
    case TRANSFER_IN = 'transfer_in';
    case TRANSFER_OUT = 'transfer_out';
    case DAMAGE = 'damage';            // ✅ Daño/merma
    case EXPIRATION = 'expiration';    // ✅ Vencimiento
    case INSTALLATION = 'installation';
    case CONSUMPTION = 'consumption';
    case PRODUCTION = 'production';
    case COUNT = 'count';
    case RELOCATION = 'relocation';
}
```

**✅ INCLUYE DEVOLUCIONES**: `MovementType::RETURN`

**Comportamiento Hardcodeado**:
- ✅ `addsStock()` - Define si suma stock
- ✅ `removesStock()` - Define si resta stock
- ✅ `affectsReservation()` - Define si afecta reservas
- ✅ `getQuantityMultiplier()` - +1, -1, o 0

**Limitación**: No puedes agregar `MovementType::CUSTOM_X` sin modificar el Enum.

---

## 🔓 Lo que SÍ es Flexible

### 1. **Meta Data (JSON) - Campo Totalmente Flexible**

```php
// Cualquier movimiento puede tener datos personalizados
$movement = new Movement(
    type: MovementType::RESERVE,
    meta: [
        'customer_id' => '12345',
        'order_id' => 'ORD-2024-001',
        'priority' => 'high',
        'shipping_method' => 'express',
        'custom_field_1' => 'valor personalizado',
        'custom_field_2' => ['nested' => 'data']
    ]
);
```

**✅ Completamente flexible** - Puedes poner cualquier dato en `meta`.

### 2. **Reference Types - String Libre**

```php
// NO está limitado a valores fijos
$movement = new Movement(
    referenceType: 'sales_order',      // predeterminado
    referenceType: 'purchase_order',   // predeterminado
    referenceType: 'return_order',     // predeterminado
    referenceType: 'mi_tipo_custom',   // ✅ PUEDES usar cualquier string
    referenceType: 'integracion_erp_x',
    referenceType: 'proceso_manufactura',
);
```

**✅ Completamente flexible** - Solo recomendaciones, no restricciones.

### 3. **Reason - Texto Libre**

```php
$movement = new Movement(
    reason: 'Devolución por defecto de fabricación',
    reason: 'Cliente cambió de opinión',
    reason: 'Daño en transporte',
    reason: 'Cualquier texto que necesites'
);
```

### 4. **Validaciones Configurables**

```php
$service = new StockMovementService(
    movementRepository: $repo,
    stockItemRepository: $stockRepo,
    allowNegativeStock: true,  // ✅ Configurable por workspace
);
```

---

## 📊 Flujos de Devoluciones

### ✅ Devolución de Cliente (INCLUIDA)

```php
// Factory helper
$movement = $factory->createCustomerReturn(
    itemId: 'ITEM-001',
    locationId: 'WAREHOUSE-MAIN',
    quantity: 5,
    returnOrderId: 'RET-2024-001',
    reason: 'Cliente no satisfecho'
);

// Resultado:
// - quantity += 5 (SUMA al stock)
// - reserved_quantity: sin cambios
// - type: 'return'
// - referenceType: 'return_order'
```

**Endpoint**:
```bash
POST /api/v1/stock/movements
{
  "type": "return",
  "item_id": "ITEM-001",
  "location_id": "WAREHOUSE-MAIN",
  "quantity": 5,
  "reference_type": "return_order",
  "reference_id": "RET-2024-001",
  "reason": "Cliente no satisfecho"
}
```

### ✅ Otros Flujos Incluidos

#### Daño/Merma
```php
$factory->createDamage(
    itemId: 'ITEM-001',
    locationId: 'WAREHOUSE-MAIN',
    quantity: 3,
    reason: 'Daño en transporte'
);
// - quantity -= 3 (RESTA del stock)
```

#### Vencimiento
```php
$movement = new Movement(
    type: MovementType::EXPIRATION,
    itemId: 'ITEM-001',
    locationId: 'WAREHOUSE-MAIN',
    quantity: 10,
    reason: 'Producto vencido'
);
// - quantity -= 10
```

#### Producción (Entrada)
```php
$movement = new Movement(
    type: MovementType::PRODUCTION,
    itemId: 'ITEM-FINISHED',
    locationId: 'PRODUCTION-AREA',
    quantity: 50,
    referenceType: 'production_order',
    referenceId: 'PROD-2024-001'
);
// - quantity += 50
```

---

## 🔄 Extender el Sistema (2 opciones)

### Opción A: Usar `meta` (Sin Código)

```php
// Crear flujo "Préstamo a Cliente" usando SHIPMENT + meta
POST /api/v1/stock/movements
{
  "type": "shipment",
  "item_id": "ITEM-001",
  "location_id": "WAREHOUSE-MAIN",
  "quantity": 2,
  "reference_type": "customer_loan",  // ✅ Custom
  "reference_id": "LOAN-2024-001",
  "meta": {
    "loan_type": "demo",
    "expected_return_date": "2024-12-31",
    "customer_id": "CUST-123"
  }
}

// Devolver préstamo
POST /api/v1/stock/movements
{
  "type": "return",
  "item_id": "ITEM-001",
  "location_id": "WAREHOUSE-MAIN",
  "quantity": 2,
  "reference_type": "loan_return",  // ✅ Custom
  "reference_id": "LOAN-2024-001",
  "meta": {
    "condition": "good",
    "returned_by": "CUST-123"
  }
}
```

**✅ No requiere cambios en código** - Solo usar campos existentes creativamente.

### Opción B: Agregar Tipo al Enum (Requiere Código)

```php
// 1. Agregar al Enum
enum MovementType: string
{
    // ... tipos existentes
    case CUSTOMER_LOAN = 'customer_loan';  // ✅ Nuevo
    case LOAN_RETURN = 'loan_return';      // ✅ Nuevo
}

// 2. Definir comportamiento
public function addsStock(): bool
{
    return match ($this) {
        self::RECEIPT,
        self::RETURN,
        self::LOAN_RETURN,  // ✅ Suma al devolver
        // ...
        => true,
        default => false,
    };
}

public function removesStock(): bool
{
    return match ($this) {
        self::SHIPMENT,
        self::CUSTOMER_LOAN,  // ✅ Resta al prestar
        // ...
        => true,
        default => false,
    };
}

// 3. Factory helper (opcional)
public function createCustomerLoan(
    string $itemId,
    string $locationId,
    int $quantity,
    string $customerId,
    ?string $expectedReturnDate = null
): Movement {
    return new Movement(
        id: $this->idGenerator->generate(),
        type: MovementType::CUSTOMER_LOAN,
        itemId: $itemId,
        locationId: $locationId,
        quantity: $quantity,
        referenceType: 'customer_loan',
        meta: [
            'customer_id' => $customerId,
            'expected_return_date' => $expectedReturnDate
        ]
    );
}
```

---

## 🎯 Recomendaciones

### Para Flujos Simples (Devoluciones, Daños, etc.)
✅ **Usar tipos existentes + `meta` + `referenceType`**
- NO requiere cambios de código
- Totalmente flexible
- Buscar/filtrar por `meta->campo`

```php
// Buscar todas las devoluciones de cliente
$movements = MovementModel::where('reference_type', 'return_order')
    ->whereJsonContains('meta->reason', 'Cliente')
    ->get();
```

### Para Flujos Complejos (Requieren Lógica Especial)
✅ **Extender el Enum + Agregar Comportamiento**
- Necesitas lógica custom (ej: "Consignment" que suma/resta dependiendo de condiciones)
- Validaciones específicas del tipo
- Reportes específicos

---

## 📝 Resumen Final

| Aspecto | ¿Hardcodeado? | ¿Extensible? |
|---------|---------------|--------------|
| **Tipos de Movimiento (Enum)** | ✅ SÍ | ⚠️ Parcial (requiere código) |
| **Meta Data (JSON)** | ❌ NO | ✅ 100% Flexible |
| **Reference Types** | ❌ NO | ✅ 100% Flexible |
| **Reason** | ❌ NO | ✅ 100% Flexible |
| **Validaciones** | ❌ NO | ✅ Configurable |
| **Devoluciones** | ✅ Incluidas | ✅ `RETURN` type |
| **Daños/Mermas** | ✅ Incluidas | ✅ `DAMAGE` type |
| **Reservas** | ✅ Incluidas | ✅ `RESERVE/RELEASE` |

### ✅ **Devoluciones ESTÁN implementadas**

```php
// Todas estas funcionan OUT OF THE BOX:
MovementType::RETURN          // Devolución de cliente
MovementType::DAMAGE          // Daño/merma
MovementType::EXPIRATION      // Vencimiento
MovementType::RESERVE         // Reserva (bloquea stock)
MovementType::RELEASE         // Libera reserva
MovementType::ADJUSTMENT_IN   // Ajuste entrada (corrección inventario)
MovementType::ADJUSTMENT_OUT  // Ajuste salida (corrección inventario)
```

### 🔧 Para Flujos Custom
1. **Fácil**: Usa `meta` + `referenceType` con tipos existentes
2. **Completo**: Extiende el Enum (15 minutos de código)

**El sistema es flexible donde importa** (meta, referencias) y **estructurado donde debe serlo** (tipos de movimiento con comportamiento definido).
