# Vessel MCP Server

MCP (Model Context Protocol) Server para interactuar con Vessel API desde agentes de IA.

## Instalación

```bash
cd mcp-vessel
npm install
npm run build
```

## Configuración

### 1. Variables de Entorno

Crea un archivo `.env` basado en `.env.example`:

```bash
cp .env.example .env
```

Edita `.env` con tu configuración:

```env
VESSEL_API_URL=http://localhost:8000
# VESSEL_API_TOKEN=tu-token-aqui  # Si necesitas autenticación
```

### 2. Claude Desktop / MCP Config

Añade a tu `claude_desktop_config.json`:

```json
{
  "mcpServers": {
    "vessel": {
      "command": "node",
      "args": ["C:/Users/J/code/vessel/vessel/apps/mcp-vessel/dist/index.js"],
      "env": {
        "VESSEL_API_URL": "http://localhost:8000"
      }
    }
  }
}
```

**Nota**: Las variables de entorno en el archivo de configuración tienen prioridad sobre el `.env` local.

## Herramientas Disponibles (42 tools)

### Items (Catálogo) - 5 tools
- `vessel_items_list` - Lista todos los items con filtros opcionales
- `vessel_items_get` - Obtiene un item específico por ID
- `vessel_items_create` - Crea un nuevo item en el catálogo
- `vessel_items_update` - Actualiza un item existente
- `vessel_items_delete` - Elimina un item (soft delete)

### Stock (Inventario) - 6 tools
- `vessel_stock_list` - Lista el inventario físico por ubicación/item
- `vessel_stock_get` - Obtiene un registro de stock específico
- `vessel_stock_create` - Crea un registro de stock en una ubicación
- `vessel_stock_adjust` - Ajusta cantidad de stock (+ o -)
- `vessel_stock_reserve` - Reserva stock para pedidos
- `vessel_stock_release` - Libera stock previamente reservado

### Lots (Trazabilidad) - 3 tools
- `vessel_lots_list` - Lista lotes con trazabilidad
- `vessel_lots_create` - Crea un lote con número y vencimiento
- `vessel_lots_by_number` - Busca un lote por su número

### Locations (Ubicaciones) - 5 tools
- `vessel_locations_list` - Lista todas las ubicaciones/bodegas
- `vessel_locations_get` - Obtiene una ubicación específica
- `vessel_locations_create` - Crea una ubicación con jerarquía opcional
- `vessel_locations_update` - Actualiza una ubicación
- `vessel_locations_delete` - Elimina una ubicación

### Movements (Movimientos) - 7 tools
- `vessel_movements_list` - Lista movimientos de inventario con filtros
- `vessel_movements_get` - Obtiene un movimiento específico
- `vessel_movements_types` - Lista tipos de movimientos disponibles
- `vessel_movements_receipt` - Registra entrada de stock (desde proveedor)
- `vessel_movements_shipment` - Registra salida de stock (a cliente)
- `vessel_movements_transfer` - Transfiere stock entre ubicaciones
- `vessel_movements_adjustment` - Ajuste de inventario (corrección, merma, daño)

### Taxonomy (Categorización) - 6 tools
- `vessel_vocabularies_list` - Lista vocabularios de taxonomía
- `vessel_vocabularies_get` - Obtiene un vocabulario específico
- `vessel_vocabularies_create` - Crea un nuevo vocabulario
- `vessel_terms_list` - Lista términos de un vocabulario
- `vessel_terms_tree` - Obtiene árbol jerárquico de términos
- `vessel_terms_create` - Crea un término en un vocabulario

### UoM (Unidades de Medida) - 3 tools
- `vessel_uom_list` - Lista unidades de medida disponibles
- `vessel_uom_get` - Obtiene una unidad específica
- `vessel_uom_convert` - Convierte cantidades entre unidades

### Capacity (Capacidad) - 2 tools
- `vessel_capacity_get` - Obtiene configuración de capacidad de ubicación
- `vessel_capacity_stats` - Estadísticas de uso de capacidad

## Recursos MCP

- `vessel://items` - Items del catálogo
- `vessel://stock` - Inventario físico
- `vessel://lots` - Lotes con trazabilidad
- `vessel://locations` - Ubicaciones y bodegas
- `vessel://movements` - Historial de movimientos
- `vessel://taxonomy` - Vocabularios y términos
- `vessel://uom` - Unidades de medida

## Casos de uso

### Consultar inventario
```
¿Cuánto stock tengo en la bodega principal?
```

### Crear y mover stock
```
Recibe 100 unidades del item X en la ubicación Y
Transfiere 50 unidades de la bodega A a la bodega B
```

### Gestión de lotes
```
Crea un lote número L-2024-001 con vencimiento en 6 meses
¿Qué lotes están por vencer?
```

### Reportes
```
Lista los últimos movimientos de este item
¿Cuál es la capacidad disponible en cada bodega?
```

## Desarrollo

```bash
# Modo desarrollo con hot-reload
npm run dev

# Build para producción
npm run build

# Ejecutar
npm start
```
- `vessel://uom` - Unidades de medida

## Configuración

El servidor usa `VESSEL_API_URL` (default: `http://localhost:8000`)

## Uso con Claude/Gemini

Agrega el MCP en la configuración de tu agente:

```json
{
  "mcpServers": {
    "vessel": {
      "command": "node",
      "args": ["C:/Users/J/code/fablab/fablab-web/mcp-vessel/dist/index.js"],
      "env": {
        "VESSEL_API_URL": "http://localhost:8000"
      }
    }
  }
}
```
