# Stock Statuses & Location Rules - Ejemplos Reales

## 1. Stock Statuses (Estados)

### Ejemplo 1: E-commerce con reservas
```sql
-- Estado: Disponible
INSERT INTO stock_statuses VALUES (uuid(), 'ws-ecommerce', 'available', 'Disponible', 'Stock disponible para venta', true, false, 1, NOW(), NOW());

-- Reglas para "available"
INSERT INTO stock_status_rules VALUES 
(uuid(), 'status-available-id', 'allow_movements', true, NULL, NOW(), NOW()),
(uuid(), 'status-available-id', 'allow_reservations', true, NULL, NOW(), NOW()),
(uuid(), 'status-available-id', 'blocks_availability', false, 'No bloquea, cuenta como disponible', NOW(), NOW());

-- Estado: Reservado (carrito activo)
INSERT INTO stock_statuses VALUES (uuid(), 'ws-ecommerce', 'reserved', 'Reservado', 'En carrito de compra activo', true, false, 2, NOW(), NOW());

-- Reglas para "reserved"
INSERT INTO stock_status_rules VALUES 
(uuid(), 'status-reserved-id', 'allow_movements', false, 'Bloqueado hasta checkout o timeout', NOW(), NOW()),
(uuid(), 'status-reserved-id', 'blocks_availability', true, 'No disponible para otros', NOW(), NOW()),
(uuid(), 'status-reserved-id', 'requires_approval', false, NULL, NOW(), NOW());

-- Transición: available → reserved (al agregar al carrito)
INSERT INTO stock_status_transitions VALUES 
(uuid(), 'status-available-id', 'status-reserved-id', false, 'add_to_cart', 1, NOW(), NOW());

-- Transición: reserved → available (timeout carrito o cancelar)
INSERT INTO stock_status_transitions VALUES 
(uuid(), 'status-reserved-id', 'status-available-id', false, 'cart_expired', 1, NOW(), NOW());
```

**Aplicación**: Cuando agregas producto al carrito, cambia a "reserved". Si no pagas en 15min, auto-transiciona a "available".

---

### Ejemplo 2: Farmacéutica con cuarentena
```sql
-- Estado: Cuarentena
INSERT INTO stock_statuses VALUES (uuid(), 'ws-pharma', 'quarantine', 'Cuarentena', 'Esperando análisis de calidad', true, false, 3, NOW(), NOW());

-- Reglas
INSERT INTO stock_status_rules VALUES 
(uuid(), 'status-quarantine-id', 'allow_movements', false, 'Bloqueado hasta QA', NOW(), NOW()),
(uuid(), 'status-quarantine-id', 'blocks_availability', true, 'No disponible', NOW(), NOW()),
(uuid(), 'status-quarantine-id', 'requires_approval', true, 'Necesita aprobación QA', NOW(), NOW());

-- Transiciones permitidas
INSERT INTO stock_status_transitions VALUES 
(uuid(), 'status-quarantine-id', 'status-available-id', true, 'qa_approved', 1, NOW(), NOW()),
(uuid(), 'status-quarantine-id', 'status-disposed-id', true, 'qa_rejected', 1, NOW(), NOW());
```

**Aplicación**: Lote llega → cuarentena automática → QA aprueba → pasa a disponible.

---

### Ejemplo 3: Manufactura con WIP (Work in Progress)
```sql
-- Estado: En producción
INSERT INTO stock_statuses VALUES (uuid(), 'ws-factory', 'in_production', 'En Producción', 'Material en proceso de fabricación', true, false, 4, NOW(), NOW());

-- Reglas
INSERT INTO stock_status_rules VALUES 
(uuid(), 'status-production-id', 'allow_movements', false, 'Bloqueado durante manufactura', NOW(), NOW()),
(uuid(), 'status-production-id', 'blocks_availability', true, 'No cuenta como inventario terminado', NOW(), NOW()),
(uuid(), 'status-production-id', 'allow_reservations', false, NULL, NOW(), NOW());

-- Transición: raw_material → in_production (al iniciar orden)
-- Transición: in_production → available (al completar)
```

**Aplicación**: Materia prima entra a producción, se bloquea. Al terminar, pasa a producto terminado disponible.

---

## 2. Location Rules (Reglas de Ubicación)

### Ejemplo 1: Almacén con capacidad máxima
```sql
-- Tipo: Estantería
INSERT INTO location_types VALUES (uuid(), 'ws-warehouse', 'shelf', 'Estantería', 'Espacio de almacenamiento estándar', true, false, NOW(), NOW());

-- Ubicación: Estante A1
INSERT INTO locations VALUES (uuid(), 'ws-warehouse', 'type-shelf-id', NULL, 'A1', '...', 0, '/A1', NOW(), NOW());

-- Regla: Capacidad máxima
INSERT INTO location_rules VALUES 
(uuid(), 'ws-warehouse', 'location-A1-id', NULL, 'capacity_limit', 'max_quantity', '1000', true, 1, NOW(), NOW()),
(uuid(), 'ws-warehouse', 'location-A1-id', NULL, 'capacity_limit', 'max_weight_kg', '500', true, 2, NOW(), NOW());
```

**Aplicación**: Estante A1 no acepta más de 1000 unidades o 500kg. Sistema rechaza movimiento si excede.

---

### Ejemplo 2: Zona refrigerada solo para perecederos
```sql
-- Tipo: Refrigerado
INSERT INTO location_types VALUES (uuid(), 'ws-food', 'cold_storage', 'Cámara Fría', 'Temperatura controlada', true, false, NOW(), NOW());

-- Regla: Solo items perecederos
INSERT INTO location_rules VALUES 
(uuid(), 'ws-food', NULL, 'type-cold-id', 'allowed_item_types', 'item_type_allowed', 'perishable', true, 1, NOW(), NOW()),
(uuid(), 'ws-food', NULL, 'type-cold-id', 'temperature_check', 'min_temp_celsius', '-5', true, 2, NOW(), NOW()),
(uuid(), 'ws-food', NULL, 'type-cold-id', 'temperature_check', 'max_temp_celsius', '5', true, 3, NOW(), NOW());
```

**Aplicación**: Cualquier ubicación tipo "cold_storage" valida temperatura y rechaza items no perecederos.

---

### Ejemplo 3: Staging auto-libera en 24h
```sql
-- Tipo: Staging (preparación de envíos)
INSERT INTO location_types VALUES (uuid(), 'ws-logistics', 'staging', 'Área de Picking', 'Preparación de pedidos', true, false, NOW(), NOW());

-- Regla: Auto-transición después de tiempo
INSERT INTO location_rules VALUES 
(uuid(), 'ws-logistics', NULL, 'type-staging-id', 'auto_status_transition', 'from_status', 'in_transit', true, 1, NOW(), NOW()),
(uuid(), 'ws-logistics', NULL, 'type-staging-id', 'auto_status_transition', 'to_status', 'available', true, 2, NOW(), NOW()),
(uuid(), 'ws-logistics', NULL, 'type-staging-id', 'auto_status_transition', 'after_hours', '24', true, 3, NOW(), NOW());
```

**Aplicación**: Items en staging con status "in_transit" se auto-liberan a "available" después de 24h si no se mueven.

---

### Ejemplo 4: Jerarquía con herencia de reglas
```sql
-- Bodega principal
INSERT INTO locations VALUES (uuid(), 'ws-multi', NULL, NULL, 'Bodega Norte', '...', 0, '/bodega-norte', NOW(), NOW());

-- Pasillo 1 (hijo de bodega)
INSERT INTO locations VALUES (uuid(), 'ws-multi', NULL, 'bodega-norte-id', 'Pasillo 1', '...', 1, '/bodega-norte/pasillo-1', NOW(), NOW());

-- Estante 1A (hijo de pasillo)
INSERT INTO locations VALUES (uuid(), 'ws-multi', 'type-shelf-id', 'pasillo-1-id', 'Estante 1A', '...', 2, '/bodega-norte/pasillo-1/1A', NOW(), NOW());

-- Regla heredada: Todo lo que esté en Bodega Norte tiene restricción de peso
INSERT INTO location_rules VALUES 
(uuid(), 'ws-multi', 'bodega-norte-id', NULL, 'capacity_limit', 'max_weight_per_child', '200', true, 1, NOW(), NOW());
```

**Aplicación**: Bodega tiene límite 200kg por estante. Todos los hijos heredan y validan.

---

## Resumen de Ventajas

✅ **Multitenant**: `workspace_id` en todas las tablas
✅ **UUIDs**: Identificadores universales, no secuenciales
✅ **Reglas normalizadas**: No JSON, consultas SQL directas
✅ **Configurables**: Sin hardcodear, se definen por tenant
✅ **Jerarquías**: Locations con parent_id, level, path
✅ **Transiciones controladas**: Estados con reglas de cambio
✅ **Prioridades**: Múltiples reglas con orden de evaluación
