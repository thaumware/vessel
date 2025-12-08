# Guía de Extensibilidad: Movimientos Custom en Vessel Stock

## 🎯 Problema Resuelto

**Antes**: Para agregar un nuevo tipo de movimiento (ej: préstamos a cliente, consignación), había que modificar el enum `MovementType` y desplegar código.

**Ahora**: Puedes agregar tipos custom desde **Infraestructura** sin tocar **Domain**.

---

## 🏗️ Arquitectura

### Domain (Rígido - NO modificar)
```
MovementType (enum)          → 18 tipos estándar + CUSTOM
StockMovementService         → Procesa movements
MovementHandlerInterface     → Contrato para extensiones
```

### Infrastructure (Flexible - EXTENDER aquí)
```
MovementHandlerRegistry      → Registro de handlers custom
CustomerLoanHandler          → Ejemplo: préstamos a cliente
ConsignmentHandler           → Ejemplo: consignación
StockServiceProvider         → Registra handlers
```

---

## ✅ Cómo Agregar un Tipo Custom

### 1. Crear el Handler

```php
<?php

namespace App\Stock\Infrastructure\Handlers;

use App\Stock\Domain\Interfaces\MovementHandlerInterface;
use App\Stock\Domain\Entities\Movement;
use App\Stock\Domain\Entities\StockItem;

class RepairInHandler implements MovementHandlerInterface
{
    public function supports(string $movementType): bool
    {
        return $movementType === 'repair_in';
    }

    public function validate(Movement $movement, StockItem $stockItem): void
    {
        // Validaciones custom
        $meta = $movement->getMeta();
        if (!isset($meta['repair_order_id'])) {
            throw new \DomainException('repair_order_id es requerido');
        }
    }

    public function handle(Movement $movement, StockItem $stockItem): StockItem
    {
        // IMPORTANTE: StockItem es inmutable, retornar el nuevo objeto
        return $stockItem->adjustQuantity($movement->getQuantity());
    }

    public function describe(): string
    {
        return 'Repair In Handler: Entrada de items desde reparación';
    }
}
```

### 2. Registrar en ServiceProvider

```php
// En StockServiceProvider.php, método register()

$this->app->singleton(MovementHandlerRegistry::class, function ($app) {
    $registry = new MovementHandlerRegistry();
    
    // Handlers EJEMPLO (descomenta para activar)
    // $registry->register(new CustomerLoanHandler());
    // $registry->register(new ConsignmentHandler());
    
    // ✅ AGREGAR TU HANDLER AQUÍ
    $registry->register(new RepairInHandler());
    
    return $registry;
});
```

### 3. Usar desde API

```bash
POST /api/v1/stock/movements
{
  "type": "custom",                  # ✅ MovementType::CUSTOM
  "item_id": "ITEM-001",
  "location_id": "SERVICE-CENTER",
  "quantity": 1,
  "reference_type": "repair_in",     # ✅ Identifica el handler
  "reference_id": "REPAIR-2024-001",
  "meta": {
    "repair_order_id": "RO-12345",
    "technician": "John Doe",
    "condition": "refurbished"
  }
}
```

---

## 📦 Handlers Incluidos (Ejemplos)

### CustomerLoanHandler
**Tipos**: `customer_loan`, `loan_return`

```bash
# Préstamo
POST /api/v1/stock/movements
{
  "type": "custom",
  "reference_type": "customer_loan",
  "quantity": 5,
  "meta": {
    "customer_id": "CUST-123",
    "expected_return_date": "2024-12-31"
  }
}

# Devolución
POST /api/v1/stock/movements
{
  "type": "custom",
  "reference_type": "loan_return",
  "quantity": 5,
  "reference_id": "LOAN-2024-001"
}
```

### ConsignmentHandler
**Tipos**: `consignment_out`, `consignment_return`

```bash
# Envío en consignación
POST /api/v1/stock/movements
{
  "type": "custom",
  "reference_type": "consignment_out",
  "quantity": 20,
  "meta": {
    "consignee": "RETAIL-STORE-001",
    "agreement_expires": "2025-01-31"
  }
}

# Devolución (no vendido)
POST /api/v1/stock/movements
{
  "type": "custom",
  "reference_type": "consignment_return",
  "quantity": 8,
  "meta": {
    "sold": false,
    "reason": "No vendido en tienda"
  }
}
```

---

## 🧪 Testing

```php
use App\Stock\Tests\Feature\CustomMovementHandlersTest;

// 7 tests, 17 assertions ✅ PASSING
test_custom_loan_handler_reduces_stock()
test_custom_loan_return_adds_stock()
test_custom_consignment_out_reduces_stock()
test_custom_consignment_return_adds_stock()
test_validation_fails_for_insufficient_stock_in_custom_handler()
test_multiple_custom_handlers_can_coexist()
test_standard_enum_types_still_work_with_custom_handlers_registered()
```

---

## 🔑 Puntos Clave

### 1. **MovementType::CUSTOM es genérico**
- No modifica el enum
- `referenceType` identifica el tipo específico

### 2. **StockItem es INMUTABLE**
```php
// ❌ MAL
public function handle(Movement $movement, StockItem $stockItem): StockItem
{
    $stockItem->quantity -= 10; // ❌ No funciona
    return $stockItem;
}

// ✅ BIEN
public function handle(Movement $movement, StockItem $stockItem): StockItem
{
    return $stockItem->adjustQuantity(-10); // ✅ Retorna nuevo objeto
}
```

### 3. **Validación Custom**
```php
public function validate(Movement $movement, StockItem $stockItem): void
{
    // Lanza DomainException si falla
    if ($stockItem->getAvailableQuantity() < $movement->getQuantity()) {
        throw new \DomainException('Stock insuficiente');
    }
}
```

### 4. **Handlers son opcionales**
- Si NO hay handler para `referenceType`, usa lógica estándar del enum
- Puedes registrar múltiples handlers (coexisten)

---

## 🚀 Activar Handlers Ejemplo

En `StockServiceProvider.php`, línea ~95:

```php
$registry = new MovementHandlerRegistry();

// ✅ DESCOMENTA PARA ACTIVAR
$registry->register(new CustomerLoanHandler());
$registry->register(new ConsignmentHandler());
```

---

## 📊 Comparación

| Aspecto | Antes | Ahora |
|---------|-------|-------|
| **Agregar tipo** | Modificar enum + deploy | Crear handler + registrar |
| **Validaciones custom** | ❌ Hardcodeadas | ✅ En handler |
| **Deploy requerido** | ✅ Sí (código core) | ⚠️ Solo si es nuevo handler |
| **Testing** | ❌ Acoplado | ✅ Aislado por handler |
| **Tipos soportados** | 18 fijos | 18 + ∞ custom |

---

## 🎓 Cuándo Usar Qué

### Usar Enum Estándar (18 tipos)
- ✅ Movimientos comunes (receipt, shipment, return, reserve, etc.)
- ✅ Sin validaciones especiales
- ✅ Comportamiento estándar (suma/resta stock)

### Usar Handler Custom
- ✅ Lógica de negocio especial
- ✅ Validaciones complejas (ej: validar contra API externa)
- ✅ Metadata custom requerida
- ✅ Comportamiento condicional (ej: "suma O resta según condición")

### Usar Meta + ReferenceType (sin handler)
- ✅ Solo necesitas metadata extra
- ✅ Comportamiento estándar (suma/resta)
- ✅ No requiere validaciones custom

**Ejemplo SIN handler**:
```bash
POST /api/v1/stock/movements
{
  "type": "shipment",               # Tipo estándar
  "reference_type": "demo_shipment", # Custom, pero usa lógica estándar
  "meta": {
    "is_demo": true,
    "customer_type": "trial"
  }
}
```

---

## ✅ Checklist: Agregar Tipo Custom

- [ ] Crear `MyCustomHandler.php` en `app/Stock/Infrastructure/Handlers/`
- [ ] Implementar `supports(string $movementType): bool`
- [ ] Implementar `validate(Movement, StockItem): void` (lanza DomainException si falla)
- [ ] Implementar `handle(Movement, StockItem): StockItem` (RETORNAR nuevo StockItem)
- [ ] Implementar `describe(): string`
- [ ] Registrar en `StockServiceProvider::register()`
- [ ] Crear test en `Tests/Feature/`
- [ ] Documentar tipo custom en README del proyecto

---

## 📝 Resumen

**Domain** = Rígido (18 tipos enum + CUSTOM genérico)  
**Infrastructure** = Flexible (handlers ilimitados)  
**Extensión** = Fácil (crear handler + registrar)  
**Testing** = 7 tests passing ✅
