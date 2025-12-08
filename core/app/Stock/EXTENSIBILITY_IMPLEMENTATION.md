# ✅ Sistema de Stock Extensible - Implementación Completa

## Problema Resuelto

**Pregunta original**: "¿Está hardcodeado? ¿Es flexible? ¿Las devoluciones cómo se manejan?"

**Respuesta**: 
- ✅ **Domain está hardcodeado** (18 tipos enum) - Por diseño
- ✅ **Infrastructure es extensible** (handlers ilimitados) - Solución implementada
- ✅ **Devoluciones funcionan** (MovementType::RETURN + handler custom para casos especiales)

---

## Implementación

### Archivos Creados

#### Domain (Contrato)
- `app/Stock/Domain/Interfaces/MovementHandlerInterface.php`
  - Interface para handlers extensibles
  - `supports()`, `validate()`, `handle()`, `describe()`

#### Infrastructure (Extensión)
- `app/Stock/Infrastructure/Services/MovementHandlerRegistry.php`
  - Registro de handlers custom
  - `register()`, `findHandler()`, `all()`

- `app/Stock/Infrastructure/Handlers/CustomerLoanHandler.php`
  - Ejemplo: préstamos a cliente (`customer_loan`, `loan_return`)

- `app/Stock/Infrastructure/Handlers/ConsignmentHandler.php`
  - Ejemplo: consignación (`consignment_out`, `consignment_return`)

#### Tests
- `app/Stock/Tests/Feature/CustomMovementHandlersTest.php`
  - 7 tests, 17 assertions ✅ PASSING

#### Documentación
- `STOCK_FLEXIBILITY_ANALYSIS.md` - Análisis completo
- `EXTENSIBILITY_GUIDE.md` - Guía paso a paso

### Cambios en Código Existente

#### MovementType.php (Domain)
```php
enum MovementType: string
{
    // ... 17 tipos existentes
    case CUSTOM = 'custom'; // ✅ NUEVO
}
```

#### StockMovementService.php (Domain)
```php
// ✅ NUEVO: Soporte para handlers custom
public function __construct(
    // ... parámetros existentes
    array $customHandlers = []  // ✅ NUEVO
) {
    $this->customHandlers = $customHandlers;
}

// ✅ NUEVO: Lógica para detectar y usar handlers
private function findHandler(Movement $movement): ?MovementHandlerInterface
private function validateMovement(Movement $movement, StockItem $stockItem): ValidationResult
```

#### StockServiceProvider.php (Infrastructure)
```php
// ✅ NUEVO: Registro de handlers
$this->app->singleton(MovementHandlerRegistry::class, function ($app) {
    $registry = new MovementHandlerRegistry();
    
    // Descomenta para activar handlers ejemplo:
    // $registry->register(new CustomerLoanHandler());
    // $registry->register(new ConsignmentHandler());
    
    return $registry;
});
```

---

## Uso

### Tipo Estándar (Devolución)
```bash
POST /api/v1/stock/movements
{
  "type": "return",              # MovementType::RETURN
  "item_id": "ITEM-001",
  "location_id": "WAREHOUSE-MAIN",
  "quantity": 5,
  "reference_type": "return_order",
  "reference_id": "RET-2024-001",
  "reason": "Cliente insatisfecho"
}
```

### Tipo Custom (Préstamo)
```bash
POST /api/v1/stock/movements
{
  "type": "custom",              # MovementType::CUSTOM
  "item_id": "ITEM-001",
  "location_id": "WAREHOUSE-MAIN",
  "quantity": 2,
  "reference_type": "customer_loan",  # Identifica handler
  "reference_id": "LOAN-2024-001",
  "meta": {
    "customer_id": "CUST-123",
    "expected_return_date": "2024-12-31"
  }
}
```

---

## Testing

```bash
$ vendor/bin/phpunit app/Stock/Tests/Feature/CustomMovementHandlersTest.php

Custom Movement Handlers
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

## Decisiones de Diseño

### 1. ¿Por qué CUSTOM en vez de strings libres?

**Alternativa rechazada**: Permitir `MovementType::from('any_string')`
- ❌ Rompe el enum (enums son cerrados por diseño)
- ❌ Pierde type safety en el Domain

**Solución elegida**: `MovementType::CUSTOM + referenceType`
- ✅ Enum sigue siendo cerrado (type safe)
- ✅ Infrastructure identifica handlers por `referenceType`
- ✅ Domain NO conoce tipos custom (separación limpia)

### 2. ¿Por qué StockItem es inmutable?

```php
// ❌ MAL
public function handle(Movement $m, StockItem $s): void {
    $s->quantity -= 10; // NO funciona
}

// ✅ BIEN
public function handle(Movement $m, StockItem $s): StockItem {
    return $s->adjustQuantity(-10); // Retorna nuevo objeto
}
```

**Razón**: Event Sourcing / Auditoría  
- Cada cambio crea nuevo objeto
- Se puede rastrear el estado anterior
- Thread-safe (no mutable state compartido)

### 3. ¿Por qué Registry en vez de auto-discovery?

**Alternativa rechazada**: Escanear directorio `Handlers/` automáticamente
- ❌ Carga handlers no necesarios
- ❌ Difícil testear en aislamiento
- ❌ Magia implícita (menos claro)

**Solución elegida**: Registro explícito en ServiceProvider
- ✅ Control total sobre qué handlers cargar
- ✅ Fácil deshabilitar handler (comentar línea)
- ✅ Explícito = fácil de entender

---

## Próximos Pasos (Opcional)

### 1. Activar Handlers Ejemplo
```php
// En StockServiceProvider.php línea ~95
$registry->register(new CustomerLoanHandler());
$registry->register(new ConsignmentHandler());
```

### 2. Agregar Handlers Propios
- Ver `EXTENSIBILITY_GUIDE.md` para paso a paso
- Crear en `app/Stock/Infrastructure/Handlers/`
- Registrar en `StockServiceProvider`

### 3. Integración con API
- Endpoint ya soporta `type: "custom"`
- Solo agregar validación de `referenceType` si es necesario

---

## Documentos Relacionados

- **STOCK_FLEXIBILITY_ANALYSIS.md**: Análisis completo de flexibilidad
- **EXTENSIBILITY_GUIDE.md**: Guía paso a paso para agregar handlers
- **CustomMovementHandlersTest.php**: Tests de referencia

---

## Métricas

- **Archivos creados**: 6
- **Archivos modificados**: 3
- **Tests**: 7 passing
- **Líneas de código**: ~800
- **Tiempo de implementación**: 1 sesión
- **Breaking changes**: 0 (backward compatible)

---

## Resumen Ejecutivo

✅ **Domain**: Rígido (18 tipos + CUSTOM)  
✅ **Infrastructure**: Extensible (handlers ilimitados)  
✅ **Devoluciones**: Incluidas (RETURN type + custom handlers)  
✅ **Testing**: 7 tests passing  
✅ **Documentación**: Completa (2 guías)  
✅ **Ejemplos**: 2 handlers funcionales
