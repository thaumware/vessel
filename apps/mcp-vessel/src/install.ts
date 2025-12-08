/**
 * Script de instalación del MCP Vessel
 * Configura automáticamente el MCP en Gemini/Claude
 */

import { readFileSync, writeFileSync, existsSync, mkdirSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { homedir } from 'node:os';
import { fileURLToPath } from 'node:url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

// Detectar ruta del MCP
const mcpDir = join(__dirname, '..');
const distPath = join(mcpDir, 'dist', 'index.js');

// Configuración del MCP
const mcpConfig = {
    vessel: {
        command: "node",
        args: [distPath.replace(/\\/g, '/')],
        env: {
            // Sin hardcodear - usa default localhost:8000 o lee de .env
        }
    }
};

// Rutas posibles de configuración
const configPaths = [
    join(homedir(), '.gemini', 'antigravity', 'mcp_config.json'),
    join(homedir(), '.claude', 'mcp_config.json'),
    join(homedir(), '.config', 'mcp', 'config.json'),
];

// Buscar archivo de config existente
let configPath = configPaths.find(p => existsSync(p));

if (!configPath) {
    // Crear en .gemini si no existe
    configPath = configPaths[0];
    const dir = dirname(configPath);
    if (!existsSync(dir)) {
        mkdirSync(dir, { recursive: true });
    }
}

// Leer config existente o crear nueva
let existingConfig: any = {};
if (existsSync(configPath)) {
    try {
        existingConfig = JSON.parse(readFileSync(configPath, 'utf-8'));
    } catch {
        existingConfig = {};
    }
}

// Merge configs
const newConfig = {
    ...existingConfig,
    mcpServers: {
        ...(existingConfig.mcpServers || {}),
        ...mcpConfig
    }
};

console.log('📦 Vessel MCP Installer');
console.log('─'.repeat(40));
console.log(`MCP Path: ${distPath}`);
console.log(`Config: ${configPath}`);
console.log('');

// Mostrar la configuración
console.log('Configuración a agregar:');
console.log(JSON.stringify(mcpConfig, null, 2));
console.log('');

// Guardar
writeFileSync(configPath, JSON.stringify(newConfig, null, 2));
console.log(`✅ MCP configurado en: ${configPath}`);
console.log('');
console.log('⚠️  Reinicia tu agente (Gemini/Claude) para usar las nuevas herramientas.');
console.log('');
console.log('Herramientas disponibles:');
console.log('  - vessel_items_list, vessel_items_create, vessel_items_update, vessel_items_delete');
console.log('  - vessel_stock_list, vessel_stock_create, vessel_stock_adjust');
console.log('  - vessel_locations_list, vessel_locations_create, vessel_locations_delete');
console.log('  - vessel_movements_list, vessel_movements_receipt, vessel_movements_consumption');
console.log('  - vessel_vocabularies_list, vessel_terms_list');
console.log('  - vessel_uom_list');
