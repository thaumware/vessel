<?php
/**
 * Script completo para crear inventario FabLab con entradas correctas
 * 1. Limpiar datos existentes
 * 2. Crear artículos en catálogo
 * 3. Crear stock items en Camptech
 * 4. Registrar movimientos de entrada
 */

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

echo "=== Creando Inventario FabLab Completo ===\n\n";

// Lista de artículos con sus cantidades de entrada
$items = [
    ['nombre' => 'Lápices Camptech', 'entrada' => 990],
    ['nombre' => 'Carpetas en general', 'entrada' => 164],
    ['nombre' => 'Portacredenciales', 'entrada' => 805],
    ['nombre' => 'Revistas Camptech', 'entrada' => 62],
    ['nombre' => 'Cartas de bodas picsart', 'entrada' => 30],
    ['nombre' => 'Manual de Logo genérico', 'entrada' => 36],
    ['nombre' => 'Manual artillery x1', 'entrada' => 1],
    ['nombre' => 'AF voca Hat', 'entrada' => 1],
    ['nombre' => 'botiquin bo sur', 'entrada' => 1],
    ['nombre' => 'Kit Cricut Maker (maquina estampados)', 'entrada' => 1],
    ['nombre' => 'Caja organizadora Trooper (vacia)', 'entrada' => 1],
    ['nombre' => 'Kit Stanleys 8 destornilladores', 'entrada' => 1],
    ['nombre' => 'Ventilador', 'entrada' => 118],
    ['nombre' => 'Kit Lentes VR Shinecon', 'entrada' => 6],
    ['nombre' => 'Luz LED Ultravioleta', 'entrada' => 1],
    ['nombre' => 'Placa VIS Arduino Nano', 'entrada' => 1],
    ['nombre' => 'Adaptador AC/DC 10 V UpBright', 'entrada' => 115],
    ['nombre' => 'Pilas PHILS Triple AAA', 'entrada' => 105],
    ['nombre' => 'Pilas Maxcell Doble AA', 'entrada' => 14],
    ['nombre' => 'Pilas Maxcell Triple AAA', 'entrada' => 16],
    ['nombre' => 'Carcasa Raspberry Pi 3', 'entrada' => 1],
    ['nombre' => 'Carcasa Raspberry Pi Rojo y Blanco', 'entrada' => 1],
    ['nombre' => 'Ventilador doble con disipador de calor', 'entrada' => 1],
    ['nombre' => 'Sensor de calibración', 'entrada' => 1],
    ['nombre' => 'Carcasa para impresora Ender 3', 'entrada' => 1],
    ['nombre' => 'Cable USB mini B', 'entrada' => 1],
    ['nombre' => 'Usb estilo esp 8266 mod', 'entrada' => 1],
    ['nombre' => 'aKY mouse keybord', 'entrada' => 1],
    ['nombre' => 'Raspberry Poli', 'entrada' => 1],
    ['nombre' => 'Raspberry Pi 3 (Unidad 1)', 'entrada' => 1],
    ['nombre' => 'Raspberry Pi 3 (Unidad 2)', 'entrada' => 1],
    ['nombre' => 'Plaza Zoom Core', 'entrada' => 1],
    ['nombre' => 'Sensor Arrange AAA', 'entrada' => 1],
    ['nombre' => 'Resina de joyas y manualidades RESINAR+ CRISTAL', 'entrada' => 2],
    ['nombre' => 'Resina de joyas y manualidades Resinart Cristal', 'entrada' => 1],
    ['nombre' => 'Spray adhesivo para impresión 3D Print a Lot', 'entrada' => 1],
    ['nombre' => 'Pegamento para tubos de PVC', 'entrada' => 1],
    ['nombre' => 'Modulo de matriz de punto LED 8x8 controlador MAX 7219', 'entrada' => 3],
    ['nombre' => 'Modulo de cámara raspberry v2', 'entrada' => 1],
    ['nombre' => 'Driver tablero paso a pasos 2PH4E530BA', 'entrada' => 2],
    ['nombre' => 'Alcohol Isop', 'entrada' => 1],
    ['nombre' => 'Cartón robot 40 pines (raspberry)', 'entrada' => 1],
    ['nombre' => 'Sensor Infrarrojo BQ', 'entrada' => 1],
    ['nombre' => 'Zoom Switch', 'entrada' => 1],
    ['nombre' => 'Spray eléctrico Tricolor', 'entrada' => 1],
    ['nombre' => 'Caja de cargador de Meta Quest 2', 'entrada' => 2],
    ['nombre' => 'Cable conector de lámpara', 'entrada' => 1],
    ['nombre' => 'balota computadoras arduino', 'entrada' => 1],
    ['nombre' => 'Módulo de cámara raspberry Pi V2', 'entrada' => 1],
    ['nombre' => 'Botón pulsador (rojo)', 'entrada' => 1],
    ['nombre' => 'Cable plano flexible FFC LVDS10 15 cm', 'entrada' => 1],
    ['nombre' => 'Cable flexible FFC CORTO 21 pines', 'entrada' => 1],
    ['nombre' => 'Cables de poder', 'entrada' => 2],
    ['nombre' => 'Fuentes de alimentación LG', 'entrada' => 1],
    ['nombre' => 'Fuentes de alimentación Samsung', 'entrada' => 4],
    ['nombre' => 'Módulo sensor temperatura BQ', 'entrada' => 1],
    ['nombre' => 'Mini servoMotor BQ Conjunto', 'entrada' => 2],
    ['nombre' => 'Zumbador BQ', 'entrada' => 2],
    ['nombre' => 'Computadores Asus', 'entrada' => 2],
    ['nombre' => 'Cargadores Asus de 19v 1.75A', 'entrada' => 1],
    ['nombre' => 'Cable VGA Video Graphics Array', 'entrada' => 2],
    ['nombre' => 'Cable Ethernet o Reel', 'entrada' => 1],
    ['nombre' => 'Mouse Ethernet', 'entrada' => 2],
    ['nombre' => 'Mouse (Eleius Asus)', 'entrada' => 1],
    ['nombre' => 'Teclado', 'entrada' => 1],
    ['nombre' => 'Chrip preago Entel', 'entrada' => 1],
    ['nombre' => 'Samsung Galaxy J4', 'entrada' => 1],
    ['nombre' => 'Samsung Galaxy s7', 'entrada' => 1],
    ['nombre' => 'Samsung Galaxy J4+', 'entrada' => 1],
    ['nombre' => 'Pantalla galaxy sin cable', 'entrada' => 1],
    ['nombre' => 'Galaxy note 10s', 'entrada' => 1],
    ['nombre' => 'Teclado lenovo', 'entrada' => 1],
    ['nombre' => 'Adaptador', 'entrada' => 1],
    ['nombre' => 'Soporte de cámara', 'entrada' => 1],
    ['nombre' => 'Módulo de sensor de medio puente de tensión de residenc', 'entrada' => 4],
    ['nombre' => 'Raspberry', 'entrada' => 1],
    ['nombre' => 'Mando', 'entrada' => 1],
    ['nombre' => 'Motor CO2', 'entrada' => 2],
    ['nombre' => 'Mini Válvula solenoide', 'entrada' => 1],
    ['nombre' => 'Servo Tornamentos', 'entrada' => 1],
    ['nombre' => 'Sensor de Temperatura y Humedad', 'entrada' => 1],
    ['nombre' => 'Sensor capacitivo de humedad del suelo', 'entrada' => 1],
    ['nombre' => 'Teclado Ups', 'entrada' => 1],
    ['nombre' => 'Sensor de pulso', 'entrada' => 1],
    ['nombre' => 'Sondas sensor de temperatura Ds18b20', 'entrada' => 6],
    ['nombre' => 'Escalador npi', 'entrada' => 2],
    ['nombre' => 'Artillería', 'entrada' => 1],
    ['nombre' => 'Portacables DARR', 'entrada' => 1],
    ['nombre' => 'Poratcables Linerar Pro G508', 'entrada' => 1],
    ['nombre' => 'Accesorios Rc Súper 3 cables servo', 'entrada' => 1],
    ['nombre' => 'Panel OLED', 'entrada' => 3],
    ['nombre' => 'Placa controladora LCD TTL LVDS', 'entrada' => 2],
    ['nombre' => 'Pantalla OLED 82', 'entrada' => 1],
    ['nombre' => 'Placa controladora LCD VGA 24V con retroiluminacion HDR', 'entrada' => 1],
    ['nombre' => 'Cable plano de datos de tarjeta SD', 'entrada' => 1],
    ['nombre' => 'Fuente de alimentación AC/DC adaptador intercambiable', 'entrada' => 1],
    ['nombre' => 'Potenciometro BQ', 'entrada' => 1],
    ['nombre' => 'Amplificador BQ', 'entrada' => 1],
    ['nombre' => 'Adaptador para crista de carga HG711', 'entrada' => 1],
    ['nombre' => 'Cable HDMI a HDM Raspberry', 'entrada' => 1],
    ['nombre' => 'Samsung Galaxy A25', 'entrada' => 1],
    ['nombre' => 'Cinta de macaras 50 piezas', 'entrada' => 1],
    ['nombre' => 'Módulo de entrada de alimentación C14 con interruptor', 'entrada' => 1],
    ['nombre' => 'Modulo ESP32', 'entrada' => 1],
    ['nombre' => 'Display oled', 'entrada' => 3],
    ['nombre' => 'Display 5 segmentos, 1 dígito', 'entrada' => 1],
    ['nombre' => 'Cargador de computador Asus', 'entrada' => 1],
    ['nombre' => 'Módulo RGB LED 3 pines', 'entrada' => 1],
    ['nombre' => 'Módulo sensor de temperatura y humedad DHT11', 'entrada' => 3],
    ['nombre' => 'Fuente de alimentación Micro USB 5ow1', 'entrada' => 1],
    ['nombre' => 'Samsung Galaxy A04 E', 'entrada' => 1],
    ['nombre' => 'Inserta para soldadura fría reforzado con acero', 'entrada' => 1],
    ['nombre' => 'Caja de cachorros 5000 unidades', 'entrada' => 1],
    ['nombre' => 'Amplificador de cable 5015 impresora 3D', 'entrada' => 1],
    ['nombre' => 'Módulo Joitic', 'entrada' => 1],
    ['nombre' => 'Módulo HFc', 'entrada' => 1],
    ['nombre' => 'Componentes tone BCB91908v1', 'entrada' => 1],
    ['nombre' => 'Adaptador HDMI a VGA', 'entrada' => 1],
    ['nombre' => 'Conector de dos pines Adhesivo PVC', 'entrada' => 3],
    ['nombre' => 'Kit de placa de extensión GPIO Rpaja Raspberry', 'entrada' => 1],
    ['nombre' => 'Raspberry (Unidad extra)', 'entrada' => 1],
    ['nombre' => 'Botón pulsador iluminado LED Rojo', 'entrada' => 1],
    ['nombre' => 'Botón pulsador iluminado LED Verde', 'entrada' => 1],
    ['nombre' => 'Botón pulsador iluminado LED Azul', 'entrada' => 1],
    ['nombre' => 'Caja plf 4 pines', 'entrada' => 1],
    ['nombre' => 'USB tipo A (macho)-MiniUSB-4 pines (impresora)', 'entrada' => 3],
    ['nombre' => 'Soldadura de estaño con flux', 'entrada' => 1],
    ['nombre' => 'Soldadura', 'entrada' => 1],
    ['nombre' => 'Pack de cinta de papel adhesivo Helian Adhesive 10 u', 'entrada' => 1],
    ['nombre' => 'Cinta de papel adhesivo Masking Tape', 'entrada' => 1],
    ['nombre' => 'Alcohol séptico', 'entrada' => 1],
    ['nombre' => 'Pistola de silicona', 'entrada' => 1],
    ['nombre' => 'Caja de marcadores extra color verde', 'entrada' => 1],
    ['nombre' => 'Caja de marcadores extra color permanente', 'entrada' => 1],
    ['nombre' => 'Notas de stickers alfa craft 100 hojas Fullons', 'entrada' => 1],
    ['nombre' => 'Caja de stickers Alfa art 100 Hojas', 'entrada' => 1],
    ['nombre' => 'Caja de blancas de tela de algodón para reprint de silicona 10 u', 'entrada' => 1],
    ['nombre' => 'Cinta para Rotuladora', 'entrada' => 1],
    ['nombre' => 'Rotuladora de pizarra magnético', 'entrada' => 1],
    ['nombre' => 'Cartón Rotunda', 'entrada' => 1],
    ['nombre' => 'Rollos para termo 10 u', 'entrada' => 1],
    ['nombre' => 'Extensión Catodico PFL + SIEMENS logo!', 'entrada' => 1],
    ['nombre' => 'Siemens SW A1 Artillery', 'entrada' => 1],
    ['nombre' => 'Artillery (Unidad extra)', 'entrada' => 1],
    ['nombre' => 'Anker Nano', 'entrada' => 1],
    ['nombre' => 'Ratn 20 pines', 'entrada' => 1],
    ['nombre' => 'Electrodos para carro', 'entrada' => 1],
    ['nombre' => 'Mecha soldadora', 'entrada' => 1],
    ['nombre' => 'Cable adaptador multipetry Mini DisplayPort a HDMI/VGA/DVI', 'entrada' => 1],
    ['nombre' => 'Caja de suministros', 'entrada' => 1],
    ['nombre' => 'Cargador de automóvil a conector DC', 'entrada' => 1],
    ['nombre' => 'Cable conectores varios', 'entrada' => 1],
    ['nombre' => 'Batería Flower Maxx SB1900-02 de aluminio 90+', 'entrada' => 1],
    ['nombre' => 'Cable de botón Q251C-02 de aluminio', 'entrada' => 1],
];

// ===== PASO 1: Limpiar datos existentes =====
echo "1. Limpiando datos existentes...\n";

DB::table('stock_movements')->delete();
echo "   ✓ Movimientos eliminados\n";

DB::table('stock_items')->delete();
echo "   ✓ Stock items eliminados\n";

DB::table('catalog_item_terms')->delete();
echo "   ✓ Términos de catálogo eliminados\n";

DB::table('catalog_items')->delete();
echo "   ✓ Artículos de catálogo eliminados\n";

DB::table('locations_locations')->delete();
echo "   ✓ Ubicaciones eliminadas\n";

// ===== PASO 2: Crear ubicación Camptech =====
echo "\n2. Creando ubicación Camptech...\n";

$locationId = (string) Str::uuid();
DB::table('locations_locations')->insert([
    'id' => $locationId,
    'name' => 'Camptech',
    'type' => 'warehouse',
    'created_at' => now(),
    'updated_at' => now(),
]);
echo "   ✓ Ubicación 'Camptech' creada (ID: {$locationId})\n";

// ===== PASO 3: Crear artículos y stock =====
echo "\n3. Creando artículos, stock y registrando entradas...\n";

$totalItems = count($items);
$created = 0;

foreach ($items as $item) {
    $catalogItemId = (string) Str::uuid();
    $stockItemId = (string) Str::uuid();
    $movementId = (string) Str::uuid();
    $now = now();

    // 3a. Crear artículo en catálogo
    DB::table('catalog_items')->insert([
        'id' => $catalogItemId,
        'name' => $item['nombre'],
        'description' => '',
        'uom_id' => null,
        'notes' => '',
        'status' => 'active',
        'workspace_id' => null,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // 3b. Crear stock item en Camptech
    DB::table('stock_items')->insert([
        'id' => $stockItemId,
        'item_id' => $item['nombre'],
        'sku' => $item['nombre'],
        'catalog_item_id' => $catalogItemId,
        'catalog_origin' => 'internal_catalog',
        'location_id' => $locationId,
        'location_type' => 'warehouse',
        'item_type' => 'unit',
        'quantity' => $item['entrada'],
        'reserved_quantity' => 0,
        'lot_number' => null,
        'expiration_date' => null,
        'serial_number' => null,
        'workspace_id' => null,
        'meta' => null,
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // 3c. Registrar movimiento de entrada
    DB::table('stock_movements')->insert([
        'id' => $movementId,
        'sku' => $item['nombre'],
        'movement_type' => 'entry',
        'status' => 'completed',
        'location_from_id' => null,
        'location_to_id' => $locationId,
        'quantity' => $item['entrada'],
        'balance_after' => $item['entrada'],
        'reference' => 'Entrada inicial de inventario FabLab',
        'user_id' => null,
        'workspace_id' => null,
        'meta' => null,
        'created_at' => $now,
        'updated_at' => $now,
        'processed_at' => $now,
    ]);

    $created++;

    // Mostrar progreso cada 20 items
    if ($created % 20 == 0 || $created == $totalItems) {
        echo "   Procesados: {$created}/{$totalItems}\n";
    }
}

echo "   ✓ {$created} artículos creados con stock y entradas registradas\n";

// ===== PASO 4: Verificar resultados =====
echo "\n4. Verificando resultados...\n";

$catalogCount = DB::table('catalog_items')->count();
$stockCount = DB::table('stock_items')->count();
$movementCount = DB::table('stock_movements')->count();
$totalStock = DB::table('stock_items')->sum('quantity');

echo "   - Artículos en catálogo: {$catalogCount}\n";
echo "   - Items en stock: {$stockCount}\n";
echo "   - Movimientos registrados: {$movementCount}\n";
echo "   - Stock total (unidades): {$totalStock}\n";

echo "\n   Primeros 10 artículos con stock:\n";
$samples = DB::table('stock_items')
    ->select('sku', 'quantity')
    ->orderBy('created_at')
    ->limit(10)
    ->get();

foreach ($samples as $sample) {
    echo "     - {$sample->sku}: {$sample->quantity} unidades\n";
}

echo "\n=== Inventario creado exitosamente ===\n";
