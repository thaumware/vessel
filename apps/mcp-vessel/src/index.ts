/**
 * Vessel MCP Server - Integración completa con Vessel API
 * 
 * Herramientas disponibles para:
 * - Items (Catálogo)
 * - Stock (Inventario físico)
 * - Lots (Trazabilidad y lotes)
 * - Locations (Bodegas y ubicaciones)
 * - Movements (Movimientos de inventario)
 * - Taxonomy (Vocabularios y términos)
 * - UoM (Unidades de medida)
 * - Capacity (Capacidad de ubicaciones)
 */

import { Server } from "@modelcontextprotocol/sdk/server/index.js";
import { StdioServerTransport } from "@modelcontextprotocol/sdk/server/stdio.js";
import {
    CallToolRequestSchema,
    ListToolsRequestSchema,
    ListResourcesRequestSchema,
    ReadResourceRequestSchema,
} from "@modelcontextprotocol/sdk/types.js";
import { config } from "dotenv";

// Cargar variables de entorno
config();

const VESSEL_API_URL = process.env.VESSEL_API_URL || "http://localhost:8000";
const VESSEL_API_TOKEN = process.env.VESSEL_API_TOKEN;

if (!process.env.VESSEL_API_URL) {
    console.warn("⚠️  VESSEL_API_URL not set, using default: http://localhost:8000");
}


// ===========================
// HTTP HELPERS
// ===========================

async function vesselGet<T>(endpoint: string, params?: Record<string, string>): Promise<T> {
    const url = new URL(`${VESSEL_API_URL}${endpoint}`);
    if (params) {
        Object.entries(params).forEach(([k, v]) => {
            if (v) url.searchParams.append(k, v);
        });
    }
    const res = await fetch(url.toString(), {
        method: "GET",
        headers: { "Content-Type": "application/json" },
    });
    if (!res.ok) throw new Error(`GET ${endpoint} failed: ${res.status} ${res.statusText}`);
    return res.json();
}

async function vesselPost<T>(endpoint: string, body: any): Promise<T> {
    const res = await fetch(`${VESSEL_API_URL}${endpoint}`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(body),
    });
    if (!res.ok) throw new Error(`POST ${endpoint} failed: ${res.status} ${res.statusText}`);
    return res.json();
}

async function vesselPut<T>(endpoint: string, body: any): Promise<T> {
    const res = await fetch(`${VESSEL_API_URL}${endpoint}`, {
        method: "PUT",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(body),
    });
    if (!res.ok) throw new Error(`PUT ${endpoint} failed: ${res.status} ${res.statusText}`);
    return res.json();
}

async function vesselDelete(endpoint: string): Promise<void> {
    const res = await fetch(`${VESSEL_API_URL}${endpoint}`, {
        method: "DELETE",
        headers: { "Content-Type": "application/json" },
    });
    if (!res.ok) throw new Error(`DELETE ${endpoint} failed: ${res.status} ${res.statusText}`);
}

// ===========================
// MCP TOOLS DEFINITION
// ===========================

const TOOLS = [
    // Items (Catálogo)
    { name: "vessel_items_list", description: "Lista items del catálogo", inputSchema: { type: "object" as const, properties: { status: { type: "string" }, search: { type: "string" } }}},
    { name: "vessel_items_get", description: "Obtiene un item por ID", inputSchema: { type: "object" as const, properties: { id: { type: "string" }}, required: ["id"] }},
    { name: "vessel_items_create", description: "Crea un item", inputSchema: { type: "object" as const, properties: { name: { type: "string" }, description: { type: "string" }, uom_id: { type: "string" }}, required: ["name"] }},
    { name: "vessel_items_update", description: "Actualiza un item", inputSchema: { type: "object" as const, properties: { id: { type: "string" }, name: { type: "string" }, description: { type: "string" }}, required: ["id"] }},
    { name: "vessel_items_delete", description: "Elimina un item", inputSchema: { type: "object" as const, properties: { id: { type: "string" }}, required: ["id"] }},

    // Stock
    { name: "vessel_stock_list", description: "Lista inventario físico", inputSchema: { type: "object" as const, properties: { location_id: { type: "string" }, catalog_item_id: { type: "string" }}}},
    { name: "vessel_stock_get", description: "Obtiene un registro de stock", inputSchema: { type: "object" as const, properties: { id: { type: "string" }}, required: ["id"] }},
    { name: "vessel_stock_create", description: "Crea stock en ubicación", inputSchema: { type: "object" as const, properties: { catalog_item_id: { type: "string" }, location_id: { type: "string" }, quantity: { type: "number" }}, required: ["catalog_item_id", "location_id", "quantity"] }},
    { name: "vessel_stock_adjust", description: "Ajusta cantidad de stock", inputSchema: { type: "object" as const, properties: { sku: { type: "string" }, location_id: { type: "string" }, delta: { type: "number" }, reason: { type: "string" }}, required: ["sku", "location_id", "delta"] }},
    { name: "vessel_stock_reserve", description: "Reserva stock", inputSchema: { type: "object" as const, properties: { id: { type: "string" }, quantity: { type: "number" }}, required: ["id", "quantity"] }},
    { name: "vessel_stock_release", description: "Libera stock reservado", inputSchema: { type: "object" as const, properties: { id: { type: "string" }, quantity: { type: "number" }}, required: ["id", "quantity"] }},

    // Lots
    { name: "vessel_lots_list", description: "Lista lotes con trazabilidad", inputSchema: { type: "object" as const, properties: { status: { type: "string" }}}},
    { name: "vessel_lots_create", description: "Crea un lote", inputSchema: { type: "object" as const, properties: { lot_number: { type: "string" }, catalog_item_id: { type: "string" }, quantity: { type: "number" }}, required: ["lot_number", "catalog_item_id", "quantity"] }},
    { name: "vessel_lots_by_number", description: "Busca lote por número", inputSchema: { type: "object" as const, properties: { lot_number: { type: "string" }}, required: ["lot_number"] }},

    // Locations
    { name: "vessel_locations_list", description: "Lista ubicaciones", inputSchema: { type: "object" as const, properties: { type: { type: "string" }}}},
    { name: "vessel_locations_get", description: "Obtiene una ubicación", inputSchema: { type: "object" as const, properties: { id: { type: "string" }}, required: ["id"] }},
    { name: "vessel_locations_create", description: "Crea ubicación", inputSchema: { type: "object" as const, properties: { name: { type: "string" }, type: { type: "string" }, parent_id: { type: "string" }}, required: ["name", "type"] }},
    { name: "vessel_locations_update", description: "Actualiza ubicación", inputSchema: { type: "object" as const, properties: { id: { type: "string" }, name: { type: "string" }}, required: ["id"] }},
    { name: "vessel_locations_delete", description: "Elimina ubicación", inputSchema: { type: "object" as const, properties: { id: { type: "string" }}, required: ["id"] }},

    // Movements
    { name: "vessel_movements_list", description: "Lista movimientos de inventario", inputSchema: { type: "object" as const, properties: { type: { type: "string" }, catalog_item_id: { type: "string" }, limit: { type: "number" }}}},
    { name: "vessel_movements_get", description: "Obtiene un movimiento", inputSchema: { type: "object" as const, properties: { id: { type: "string" }}, required: ["id"] }},
    { name: "vessel_movements_types", description: "Lista tipos de movimientos", inputSchema: { type: "object" as const, properties: {}}},
    { name: "vessel_movements_receipt", description: "Entrada de stock", inputSchema: { type: "object" as const, properties: { catalog_item_id: { type: "string" }, location_id: { type: "string" }, quantity: { type: "number" }}, required: ["catalog_item_id", "location_id", "quantity"] }},
    { name: "vessel_movements_shipment", description: "Salida de stock", inputSchema: { type: "object" as const, properties: { catalog_item_id: { type: "string" }, location_id: { type: "string" }, quantity: { type: "number" }}, required: ["catalog_item_id", "location_id", "quantity"] }},
    { name: "vessel_movements_transfer", description: "Transferencia entre ubicaciones", inputSchema: { type: "object" as const, properties: { catalog_item_id: { type: "string" }, source_location_id: { type: "string" }, destination_location_id: { type: "string" }, quantity: { type: "number" }}, required: ["catalog_item_id", "source_location_id", "destination_location_id", "quantity"] }},
    { name: "vessel_movements_adjustment", description: "Ajuste de inventario", inputSchema: { type: "object" as const, properties: { catalog_item_id: { type: "string" }, location_id: { type: "string" }, quantity: { type: "number" }, reason: { type: "string" }}, required: ["catalog_item_id", "location_id", "quantity", "reason"] }},

    // Taxonomy
    { name: "vessel_vocabularies_list", description: "Lista vocabularios", inputSchema: { type: "object" as const, properties: {}}},
    { name: "vessel_vocabularies_get", description: "Obtiene un vocabulario", inputSchema: { type: "object" as const, properties: { id: { type: "string" }}, required: ["id"] }},
    { name: "vessel_vocabularies_create", description: "Crea vocabulario", inputSchema: { type: "object" as const, properties: { name: { type: "string" }, machine_name: { type: "string" }}, required: ["name"] }},
    { name: "vessel_terms_list", description: "Lista términos", inputSchema: { type: "object" as const, properties: { vocabulary_id: { type: "string" }}}},
    { name: "vessel_terms_tree", description: "Árbol jerárquico de términos", inputSchema: { type: "object" as const, properties: { vocabulary_id: { type: "string" }}}},
    { name: "vessel_terms_create", description: "Crea término", inputSchema: { type: "object" as const, properties: { vocabulary_id: { type: "string" }, name: { type: "string" }, parent_id: { type: "string" }}, required: ["vocabulary_id", "name"] }},

    // UoM
    { name: "vessel_uom_list", description: "Lista unidades de medida", inputSchema: { type: "object" as const, properties: {}}},
    { name: "vessel_uom_get", description: "Obtiene una UoM", inputSchema: { type: "object" as const, properties: { id: { type: "string" }}, required: ["id"] }},
    { name: "vessel_uom_convert", description: "Convierte entre unidades", inputSchema: { type: "object" as const, properties: { from_uom: { type: "string" }, to_uom: { type: "string" }, quantity: { type: "number" }}, required: ["from_uom", "to_uom", "quantity"] }},

    // Capacity
    { name: "vessel_capacity_get", description: "Obtiene capacidad de ubicación", inputSchema: { type: "object" as const, properties: { location_id: { type: "string" }}, required: ["location_id"] }},
    { name: "vessel_capacity_stats", description: "Estadísticas de capacidad", inputSchema: { type: "object" as const, properties: { location_id: { type: "string" }}, required: ["location_id"] }},
];

// ===========================
// TOOL HANDLERS
// ===========================

async function handleToolCall(name: string, args: any): Promise<any> {
    const params = (p: Record<string, any>) => Object.fromEntries(Object.entries(p).filter(([_, v]) => v !== undefined).map(([k, v]) => [k, String(v)]));

    switch (name) {
        // Items
        case "vessel_items_list": return await vesselGet("/api/v1/items/read", params(args));
        case "vessel_items_get": return await vesselGet(`/api/v1/items/show/${args.id}`);
        case "vessel_items_create": return await vesselPost("/api/v1/items/create", args);
        case "vessel_items_update": return await vesselPut(`/api/v1/items/update/${args.id}`, args);
        case "vessel_items_delete": await vesselDelete(`/api/v1/items/delete/${args.id}`); return { success: true };

        // Stock
        case "vessel_stock_list": return await vesselGet("/api/v1/stock/items/read", params({ ...args, with_catalog: "true" }));
        case "vessel_stock_get": return await vesselGet(`/api/v1/stock/items/show/${args.id}`);
        case "vessel_stock_create": return await vesselPost("/api/v1/stock/items/create", { ...args, catalog_origin: "vessel_items", location_type: "warehouse" });
        case "vessel_stock_adjust": return await vesselPost("/api/v1/stock/items/adjust", args);
        case "vessel_stock_reserve": return await vesselPost(`/api/v1/stock/items/reserve/${args.id}`, args);
        case "vessel_stock_release": return await vesselPost(`/api/v1/stock/items/release/${args.id}`, args);

        // Lots
        case "vessel_lots_list": return await vesselGet("/api/v1/stock/lots/read", params(args));
        case "vessel_lots_create": return await vesselPost("/api/v1/stock/lots/create", args);
        case "vessel_lots_by_number": return await vesselGet(`/api/v1/stock/lots/by-number/${args.lot_number}`);

        // Locations
        case "vessel_locations_list": return await vesselGet("/api/v1/locations/read", params(args));
        case "vessel_locations_get": return await vesselGet(`/api/v1/locations/show/${args.id}`);
        case "vessel_locations_create": return await vesselPost("/api/v1/locations/create", args);
        case "vessel_locations_update": return await vesselPut(`/api/v1/locations/update/${args.id}`, args);
        case "vessel_locations_delete": await vesselDelete(`/api/v1/locations/delete/${args.id}`); return { success: true };

        // Movements
        case "vessel_movements_list": return await vesselGet("/api/v1/stock/movements/", params(args));
        case "vessel_movements_get": return await vesselGet(`/api/v1/stock/movements/${args.id}`);
        case "vessel_movements_types": return await vesselGet("/api/v1/stock/movements/types");
        case "vessel_movements_receipt": return await vesselPost("/api/v1/stock/movements/receipt", args);
        case "vessel_movements_shipment": return await vesselPost("/api/v1/stock/movements/shipment", args);
        case "vessel_movements_transfer": return await vesselPost("/api/v1/stock/movements/transfer", args);
        case "vessel_movements_adjustment": return await vesselPost("/api/v1/stock/movements/adjustment", args);

        // Taxonomy
        case "vessel_vocabularies_list": return await vesselGet("/api/v1/taxonomy/vocabularies/read");
        case "vessel_vocabularies_get": return await vesselGet(`/api/v1/taxonomy/vocabularies/show/${args.id}`);
        case "vessel_vocabularies_create": return await vesselPost("/api/v1/taxonomy/vocabularies/create", args);
        case "vessel_terms_list": return await vesselGet("/api/v1/taxonomy/terms/read", params(args));
        case "vessel_terms_tree": return await vesselGet("/api/v1/taxonomy/terms/tree", params(args));
        case "vessel_terms_create": return await vesselPost("/api/v1/taxonomy/terms/create", args);

        // UoM
        case "vessel_uom_list": return await vesselGet("/api/v1/uom/measures/read");
        case "vessel_uom_get": return await vesselGet(`/api/v1/uom/measures/show/${args.id}`);
        case "vessel_uom_convert": return await vesselPost("/api/v1/uom/measures/convert", args);

        // Capacity
        case "vessel_capacity_get": return await vesselGet(`/api/v1/stock/capacity/${args.location_id}`);
        case "vessel_capacity_stats": return await vesselGet(`/api/v1/stock/capacity/${args.location_id}/stats`);

        default: throw new Error(`Unknown tool: ${name}`);
    }
}

// ===========================
// MCP SERVER SETUP
// ===========================

const server = new Server({ name: "vessel-mcp", version: "1.0.0" }, { capabilities: { tools: {}, resources: {} }});

server.setRequestHandler(ListToolsRequestSchema, async () => ({ tools: TOOLS }));

server.setRequestHandler(CallToolRequestSchema, async (request) => {
    try {
        const result = await handleToolCall(request.params.name, request.params.arguments || {});
        return { content: [{ type: "text" as const, text: JSON.stringify(result, null, 2) }]};
    } catch (error: any) {
        return { content: [{ type: "text" as const, text: `Error: ${error.message}` }], isError: true };
    }
});

server.setRequestHandler(ListResourcesRequestSchema, async () => ({
    resources: [
        { uri: "vessel://items", name: "Items del Catálogo", description: "Gestión de items" },
        { uri: "vessel://stock", name: "Stock/Inventario", description: "Inventario físico" },
        { uri: "vessel://lots", name: "Lotes", description: "Trazabilidad y lotes" },
        { uri: "vessel://locations", name: "Ubicaciones", description: "Bodegas y locaciones" },
        { uri: "vessel://movements", name: "Movimientos", description: "Movimientos de inventario" },
        { uri: "vessel://taxonomy", name: "Taxonomía", description: "Vocabularios y términos" },
        { uri: "vessel://uom", name: "UoM", description: "Unidades de medida" },
    ],
}));

server.setRequestHandler(ReadResourceRequestSchema, async (request) => {
    const routes: Record<string, string> = {
        "vessel://items": "/api/v1/items/read",
        "vessel://stock": "/api/v1/stock/items/read?with_catalog=true",
        "vessel://lots": "/api/v1/stock/lots/read",
        "vessel://locations": "/api/v1/locations/read",
        "vessel://movements": "/api/v1/stock/movements/?limit=50",
        "vessel://taxonomy": "/api/v1/taxonomy/vocabularies/read",
        "vessel://uom": "/api/v1/uom/measures/read",
    };
    
    const endpoint = routes[request.params.uri];
    if (!endpoint) throw new Error(`Unknown resource: ${request.params.uri}`);
    
    const data = await vesselGet(endpoint);
    return { contents: [{ uri: request.params.uri, mimeType: "application/json", text: JSON.stringify(data, null, 2) }]};
});

// Start server
async function main() {
    const transport = new StdioServerTransport();
    await server.connect(transport);
    console.error("Vessel MCP Server running on stdio");
}

main().catch(console.error);
