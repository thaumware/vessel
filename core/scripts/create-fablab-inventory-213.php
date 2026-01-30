<?php
/**
 * Script para crear inventario FabLab con exactamente 213 artículos
 * Ordenados alfabéticamente con sus cantidades correspondientes
 */

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

echo "=== Creando Inventario FabLab - 213 Artículos ===\n\n";

// Lista de 213 artículos ordenados alfabéticamente
$items = [
    ['nombre' => 'AC Adapter 5V', 'cantidad' => 7],
    ['nombre' => 'Accesorio Rc Hosim 3 cables servo', 'cantidad' => 2],
    ['nombre' => 'Adaptador AC/DC 10 V UpBright', 'cantidad' => 10],
    ['nombre' => 'Adaptador AC/DC 240v', 'cantidad' => 1],
    ['nombre' => 'Adaptador HDMI a VGA', 'cantidad' => 1],
    ['nombre' => 'Adaptador multipuerto Mini DisplayPort a HDMI/VGA/DVI Dinon', 'cantidad' => 3],
    ['nombre' => 'Alcohol Isopropílico', 'cantidad' => 1],
    ['nombre' => 'Alargador Rittig', 'cantidad' => 1],
    ['nombre' => 'Alargadores', 'cantidad' => 4],
    ['nombre' => 'Alargadores inteligentes', 'cantidad' => 3],
    ['nombre' => 'Amplificador de celda de carga SparkFun - HX711 SEN-1879', 'cantidad' => 1],
    ['nombre' => 'Amplificador para célula de carga HX711', 'cantidad' => 3],
    ['nombre' => 'Anotador simple', 'cantidad' => 3],
    ['nombre' => 'Ampolleto DAIR', 'cantidad' => 1],
    ['nombre' => 'Archivador Torre', 'cantidad' => 3],
    ['nombre' => 'Audífonos con cable Samsung', 'cantidad' => 2],
    ['nombre' => 'AIY microphone hat', 'cantidad' => 14],
    ['nombre' => 'AIY Vision bonnet', 'cantidad' => 14],
    ['nombre' => 'AIY voice hat', 'cantidad' => 14],
    ['nombre' => 'Battery Charger AA//AAA', 'cantidad' => 3],
    ['nombre' => 'Batería Power Maxx SB1290-F2 12v 9,0Ah', 'cantidad' => 1],
    ['nombre' => 'Bolsas para basura 10 u.', 'cantidad' => 4],
    ['nombre' => 'Bolsita componentes arduino', 'cantidad' => 1],
    ['nombre' => 'Bolsita con clips de cintura', 'cantidad' => 6],
    ['nombre' => 'Bolsita de llaves allen', 'cantidad' => 1],
    ['nombre' => 'Bolsitas bio sur', 'cantidad' => 58],
    ['nombre' => 'Bolsitas de tornillos', 'cantidad' => 15],
    ['nombre' => 'Botón BQ', 'cantidad' => 13],
    ['nombre' => 'Botón pulsador iluminado LED Amarillo', 'cantidad' => 11],
    ['nombre' => 'Botón pulsador iluminado LED Blanco', 'cantidad' => 14],
    ['nombre' => 'Botón pulsador iluminado LED Rojo', 'cantidad' => 1],
    ['nombre' => 'Botón pulsador iluminado LED Verde', 'cantidad' => 2],
    ['nombre' => 'Bremen', 'cantidad' => 1],
    ['nombre' => 'Brocha', 'cantidad' => 1],
    ['nombre' => 'Cable adaptador USB a conector DC', 'cantidad' => 1],
    ['nombre' => 'Cable Banana Banana', 'cantidad' => 9],
    ['nombre' => 'Cable con conectores jack', 'cantidad' => 1],
    ['nombre' => 'Cable conector de 10 pines', 'cantidad' => 13],
    ['nombre' => 'Cable de batería estándar (color negro)', 'cantidad' => 1],
    ['nombre' => 'Cable de batería rojo', 'cantidad' => 1],
    ['nombre' => 'Cable de poder', 'cantidad' => 18],
    ['nombre' => 'Cable de poder de PC entrada americana', 'cantidad' => null],
    ['nombre' => 'Cable Ethernet (RJ45)', 'cantidad' => 2],
    ['nombre' => 'Cable flex 20 pines', 'cantidad' => 1],
    ['nombre' => 'Cable HDMI', 'cantidad' => 1],
    ['nombre' => 'Cable HDMI para elíptica Octane Fitness', 'cantidad' => 4],
    ['nombre' => 'Cable micro HDMI a HDMI Raspberry', 'cantidad' => 2],
    ['nombre' => 'Cable OTG', 'cantidad' => 5],
    ['nombre' => 'Cable plano de datos de señal 10 pines', 'cantidad' => 1],
    ['nombre' => 'Cable plano flexible (FFC) CORTO 21 pines', 'cantidad' => 12],
    ['nombre' => 'Cable plano flexible (FFC) LARGO 15 pines', 'cantidad' => 12],
    ['nombre' => 'Cable ribbon 40 pines (raspberry)', 'cantidad' => 4],
    ['nombre' => 'Cable USB mini B', 'cantidad' => 9],
    ['nombre' => 'Cable USB-A mini B Cisco', 'cantidad' => 3],
    ['nombre' => 'Cable USB a mini USB', 'cantidad' => 10],
    ['nombre' => 'Cable VGA Video Graphics Array', 'cantidad' => 2],
    ['nombre' => 'Caja de accoclips metálicos 50 u aprox', 'cantidad' => 7],
    ['nombre' => 'Caja de cargador de Meta Quest 2', 'cantidad' => 2],
    ['nombre' => 'Caja de corchetes 5000 unidades', 'cantidad' => 2],
    ['nombre' => 'Caja de lápices Bic negro 50 u.', 'cantidad' => 1],
    ['nombre' => 'Caja de lápices Bic rojos 50 u.', 'cantidad' => 2],
    ['nombre' => 'Caja de marcadores de pizarra rojo 12u.', 'cantidad' => 1],
    ['nombre' => 'Caja de marcadores de pizarra verde 12u.', 'cantidad' => 1],
    ['nombre' => 'Caja de mascarillas 50 piezas', 'cantidad' => 1],
    ['nombre' => 'Caja organizadora Truper (vacías)', 'cantidad' => 9],
    ['nombre' => 'Calculadora Casio', 'cantidad' => 1],
    ['nombre' => 'Cajas de cargador de Meta Quest 2', 'cantidad' => 2],
    ['nombre' => 'Cargador de computador LG 19v', 'cantidad' => 3],
    ['nombre' => 'Cargador Xiaomi 5v con doble puerto', 'cantidad' => 1],
    ['nombre' => 'Cargador Samsung original', 'cantidad' => 2],
    ['nombre' => 'Cargadores Asus de PC 19v 1,75A', 'cantidad' => 35],
    ['nombre' => 'Carcasa Raspberry Pi Rojo y Blanco', 'cantidad' => 1],
    ['nombre' => 'Carpetas en general', 'cantidad' => 144],
    ['nombre' => 'Carrete alargador HALUX amarillo y negro', 'cantidad' => 1],
    ['nombre' => 'Celular Samsung Galaxy A21s', 'cantidad' => 1],
    ['nombre' => 'Cepillo de alambre', 'cantidad' => 6],
    ['nombre' => 'Chip prepago Entel', 'cantidad' => 11],
    ['nombre' => 'Computadora torre 8CG9105HYR', 'cantidad' => 1],
    ['nombre' => 'Computadores Asus', 'cantidad' => 34],
    ['nombre' => 'Conector de dos pines Adhesivo PVC', 'cantidad' => 1],
    ['nombre' => 'Conector de entrada de fuente de alimentación NEGRO (negativo)', 'cantidad' => 87],
    ['nombre' => 'Conector de entrada de fuente de alimentación ROJO (positivo)', 'cantidad' => 87],
    ['nombre' => 'Controlador de motor', 'cantidad' => 1],
    ['nombre' => 'Cinta de papel adhesiva Masking Tape', 'cantidad' => 8],
    ['nombre' => 'Cables de poder', 'cantidad' => 18],
    ['nombre' => 'Diafragma de válvula de pulso', 'cantidad' => 30],
    ['nombre' => 'Diplomas Olimpiadas 2023', 'cantidad' => 9],
    ['nombre' => 'Display 7 segmentos, 1 dígito', 'cantidad' => 3],
    ['nombre' => 'Display 7 segmentos, 4 dígitos', 'cantidad' => 2],
    ['nombre' => 'Driver Motor paso a paso modelo 2PH85309A', 'cantidad' => 3],
    ['nombre' => 'Electrodo para acero al carbono 3/32\'\'', 'cantidad' => 12],
    ['nombre' => 'Endurecedor joyas y manualidades Resinart Cristal', 'cantidad' => 1],
    ['nombre' => 'Entrenador didáctico PLC SIEMENS logo!', 'cantidad' => 1],
    ['nombre' => 'Escobillón rojo', 'cantidad' => 5],
    ['nombre' => 'Esmalte sintético Tricolor', 'cantidad' => 1],
    ['nombre' => 'Estuche SW-X1 Artillery', 'cantidad' => 3],
    ['nombre' => 'Fuente de alimentación AC/DC adaptador intercambiable 12v', 'cantidad' => 1],
    ['nombre' => 'Fuente de alimentación Delta Electronics', 'cantidad' => 1],
    ['nombre' => 'Fuente de alimentación LG', 'cantidad' => 4],
    ['nombre' => 'Fuente de alimentación Micro USB Zowi', 'cantidad' => 5],
    ['nombre' => 'Fuente de alimentación Samsung', 'cantidad' => 1],
    ['nombre' => 'Fuente de poder de 12 volts', 'cantidad' => 3],
    ['nombre' => 'Galaxy Note 10s', 'cantidad' => 4],
    ['nombre' => 'HDMI premium', 'cantidad' => 4],
    ['nombre' => 'Headlights Spotlight', 'cantidad' => 13],
    ['nombre' => 'Imán', 'cantidad' => 1],
    ['nombre' => 'Jockey Biosur', 'cantidad' => 2],
    ['nombre' => 'jumper macho macho', 'cantidad' => 1],
    ['nombre' => 'Kit Cricut Maker (máquina estampados)', 'cantidad' => 1],
    ['nombre' => 'Kit de placa de extensión GPIO para Raspberry', 'cantidad' => 3],
    ['nombre' => 'Kit lentes VR Shinecon', 'cantidad' => 6],
    ['nombre' => 'Kit Stanley 6 destornilladores', 'cantidad' => 6],
    ['nombre' => 'Lápices Camptech', 'cantidad' => 960],
    ['nombre' => 'LED cableado', 'cantidad' => 13],
    ['nombre' => 'Libro de asistencia', 'cantidad' => 2],
    ['nombre' => 'Luz LED LD-160', 'cantidad' => 1],
    ['nombre' => 'Manual artillery x1', 'cantidad' => 6],
    ['nombre' => 'Manual de Lego genérico', 'cantidad' => 36],
    ['nombre' => 'Meta Quest 2', 'cantidad' => 3],
    ['nombre' => 'Micro servo Tower Pro SG90', 'cantidad' => 2],
    ['nombre' => 'Mini servomotor BQ Zumkit', 'cantidad' => 24],
    ['nombre' => 'Mini válvula solenoide', 'cantidad' => 2],
    ['nombre' => 'Mini water pump Low Noise 120L', 'cantidad' => 2],
    ['nombre' => 'Modulo ESP32', 'cantidad' => 1],
    ['nombre' => 'Modulo ESP8266MOD', 'cantidad' => 5],
    ['nombre' => 'Modulo RGB LED 3 pines', 'cantidad' => 2],
    ['nombre' => 'Modulo segmento LED', 'cantidad' => 4],
    ['nombre' => 'Modulo sensor de temperatura y humedad DHT11', 'cantidad' => 6],
    ['nombre' => 'Modulo NFC', 'cantidad' => 12],
    ['nombre' => 'Modulo cámara Raspberry Pi V2', 'cantidad' => 2],
    ['nombre' => 'Modulo cámara Raspberry Pi v2', 'cantidad' => 7],
    ['nombre' => 'Módulo de entrada de alimentación C14 con interruptor y fusible', 'cantidad' => 1],
    ['nombre' => 'Módulo de matriz de punto LED 8x8 MAX7219', 'cantidad' => 3],
    ['nombre' => 'Módulo de sensor de medio puente de tensión de resistencia', 'cantidad' => 4],
    ['nombre' => 'Monitor ELO', 'cantidad' => 1],
    ['nombre' => 'Mouse Asus', 'cantidad' => 40],
    ['nombre' => 'Mouse Genius con cable', 'cantidad' => 4],
    ['nombre' => 'Mouse Genius Wireless', 'cantidad' => 3],
    ['nombre' => 'Mouse Philco', 'cantidad' => 5],
    ['nombre' => 'Notas adhesivas amarillo 100 hojas Fultons', 'cantidad' => 40],
    ['nombre' => 'Pack de barras de silicona para pistola 10 u.', 'cantidad' => 1],
    ['nombre' => 'Pack de cinta de papel adhesiva Helian 10 u.', 'cantidad' => 4],
    ['nombre' => 'Pack de opalinas', 'cantidad' => 1],
    ['nombre' => 'Pantalla LCD 1602', 'cantidad' => 4],
    ['nombre' => 'Pantalla OLED 10 in', 'cantidad' => 1],
    ['nombre' => 'Parlante', 'cantidad' => 15],
    ['nombre' => 'Partes de servo pequeño', 'cantidad' => 24],
    ['nombre' => 'Paquetes de 6 u. Post-it 500 hojas', 'cantidad' => 11],
    ['nombre' => 'Paquetes de 6 u. Post-it 400 hojas', 'cantidad' => 1],
    ['nombre' => 'Pegamento para tubos de PVC', 'cantidad' => 1],
    ['nombre' => 'Pilas Macrotel Triple AAA', 'cantidad' => 16],
    ['nombre' => 'Pilas PHILCO Doble AA', 'cantidad' => 15],
    ['nombre' => 'Pilas PHILCO Triple AAA', 'cantidad' => 133],
    ['nombre' => 'Placa Arduino Nano', 'cantidad' => 9],
    ['nombre' => 'Placa controladora LCD TTL LVDS', 'cantidad' => 1],
    ['nombre' => 'Placa controladora LCD VGA 2AV HDMI', 'cantidad' => 1],
    ['nombre' => 'Placa Zoom Core', 'cantidad' => 13],
    ['nombre' => 'Portacredenciales', 'cantidad' => 825],
    ['nombre' => 'Portapilas', 'cantidad' => 14],
    ['nombre' => 'Potenciómetro 500K', 'cantidad' => 6],
    ['nombre' => 'Potenciómetro BQ', 'cantidad' => 13],
    ['nombre' => 'Protoboard', 'cantidad' => 4],
    ['nombre' => 'Raspberry Pi 0', 'cantidad' => 14],
    ['nombre' => 'Raspberry Pi 3', 'cantidad' => 6],
    ['nombre' => 'Raspberry Pi 4', 'cantidad' => 1],
    ['nombre' => 'Raspberry Pi Camera v2', 'cantidad' => 6],
    ['nombre' => 'Raspberry cargador', 'cantidad' => 2],
    ['nombre' => 'Resina de joyas y manualidades Resinart Cristal', 'cantidad' => 1],
    ['nombre' => 'Resina para soldadura fría reforzado con acero', 'cantidad' => 1],
    ['nombre' => 'Revistas Camptech', 'cantidad' => 62],
    ['nombre' => 'Robot Zowi', 'cantidad' => 3],
    ['nombre' => 'Rollo de stickers Fab Lab', 'cantidad' => 1],
    ['nombre' => 'Rótula de bola QZSD-02 aluminio 360°', 'cantidad' => 1],
    ['nombre' => 'Samsung Galaxy J4', 'cantidad' => 2],
    ['nombre' => 'Samsung Galaxy J4+', 'cantidad' => 3],
    ['nombre' => 'Sensor capacitivo de humedad del suelo', 'cantidad' => 8],
    ['nombre' => 'Sensor de calibración', 'cantidad' => 1],
    ['nombre' => 'Sensor de luz BQ', 'cantidad' => 14],
    ['nombre' => 'Sensor de temperatura', 'cantidad' => 5],
    ['nombre' => 'Sensor de temperatura y humedad', 'cantidad' => 1],
    ['nombre' => 'Sensor infrarrojo BQ', 'cantidad' => 28],
    ['nombre' => 'Sensor ultrasonido BQ', 'cantidad' => 13],
    ['nombre' => 'Servomotor SpringRC', 'cantidad' => 7],
    ['nombre' => 'Soporte de cámara', 'cantidad' => 1],
    ['nombre' => 'Soquete Mec', 'cantidad' => 1],
    ['nombre' => 'Soldadura fría', 'cantidad' => 1],
    ['nombre' => 'Spray adhesivo impresión 3D Print a Lot', 'cantidad' => 1],
    ['nombre' => 'Tacos de Post-it 500 hojas', 'cantidad' => 4],
    ['nombre' => 'Tacos de Post-it 400 hojas', 'cantidad' => 2],
    ['nombre' => 'Taladro percutor', 'cantidad' => 1],
    ['nombre' => 'Tarjeteros Inacap red de exalumnos', 'cantidad' => 7],
    ['nombre' => 'Teclado Genius', 'cantidad' => 9],
    ['nombre' => 'Teclado HP', 'cantidad' => 1],
    ['nombre' => 'Teclado Lenovo', 'cantidad' => 3],
    ['nombre' => 'Teclado Philco', 'cantidad' => 6],
    ['nombre' => 'Teclado Ultra', 'cantidad' => 1],
    ['nombre' => 'Tijeras', 'cantidad' => 2],
    ['nombre' => 'USB tipo A / Mini-USB 4 pines (impresora)', 'cantidad' => 7],
    ['nombre' => 'Ventilador doble con disipador de calor', 'cantidad' => null],
    ['nombre' => 'Ventilador soplador 5015 impresora 3D', 'cantidad' => 3],
    ['nombre' => 'Ventilador USB', 'cantidad' => 4],
    ['nombre' => 'Warning light', 'cantidad' => 1],
    ['nombre' => 'Zowi board BQ', 'cantidad' => 1],
    ['nombre' => 'Zumbador BQ', 'cantidad' => 13],
];

// Verificar cantidad de artículos
$totalArticulos = count($items);
echo "Total de artículos a registrar: {$totalArticulos}\n\n";

if ($totalArticulos !== 213) {
    echo "⚠️  ADVERTENCIA: Se esperaban 213 artículos pero hay {$totalArticulos}\n\n";
}

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
echo "   ✓ Ubicación 'Camptech' creada\n";

// ===== PASO 3: Crear artículos y stock =====
echo "\n3. Creando artículos, stock y registrando entradas...\n";

$created = 0;
$conStock = 0;
$sinStock = 0;

foreach ($items as $item) {
    $catalogItemId = (string) Str::uuid();
    $stockItemId = (string) Str::uuid();
    $movementId = (string) Str::uuid();
    $now = now();
    
    // Determinar cantidad (null = sin especificar = 0 para el sistema)
    $cantidad = $item['cantidad'] ?? 0;
    $cantidadDisplay = $item['cantidad'] === null ? '—' : $item['cantidad'];
    
    // 3a. Crear artículo en catálogo
    DB::table('catalog_items')->insert([
        'id' => $catalogItemId,
        'name' => $item['nombre'],
        'description' => '',
        'uom_id' => null,
        'notes' => $item['cantidad'] === null ? 'Cantidad no especificada' : '',
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
        'quantity' => $cantidad,
        'reserved_quantity' => 0,
        'lot_number' => null,
        'expiration_date' => null,
        'serial_number' => null,
        'workspace_id' => null,
        'meta' => null,
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    
    // 3c. Registrar movimiento de entrada (solo si hay cantidad)
    if ($cantidad > 0) {
        DB::table('stock_movements')->insert([
            'id' => $movementId,
            'sku' => $item['nombre'],
            'movement_type' => 'entry',
            'status' => 'completed',
            'location_from_id' => null,
            'location_to_id' => $locationId,
            'quantity' => $cantidad,
            'balance_after' => $cantidad,
            'reference' => 'Entrada inicial de inventario FabLab',
            'user_id' => null,
            'workspace_id' => null,
            'meta' => null,
            'created_at' => $now,
            'updated_at' => $now,
            'processed_at' => $now,
        ]);
        $conStock++;
    } else {
        $sinStock++;
    }
    
    $created++;
    
    // Mostrar progreso cada 50 items
    if ($created % 50 == 0 || $created == $totalArticulos) {
        echo "   Procesados: {$created}/{$totalArticulos}\n";
    }
}

echo "   ✓ {$created} artículos creados\n";
echo "   ✓ {$conStock} artículos con entrada de stock registrada\n";
echo "   ✓ {$sinStock} artículos sin cantidad especificada\n";

// ===== PASO 4: Verificar resultados =====
echo "\n4. Verificando resultados...\n";

$catalogCount = DB::table('catalog_items')->count();
$stockCount = DB::table('stock_items')->count();
$movementCount = DB::table('stock_movements')->count();
$totalStock = DB::table('stock_items')->sum('quantity');

echo "   - Artículos en catálogo: {$catalogCount}\n";
echo "   - Items en stock: {$stockCount}\n";
echo "   - Movimientos de entrada: {$movementCount}\n";
echo "   - Stock total (unidades): {$totalStock}\n";

// Verificar que sean exactamente 213
if ($catalogCount === 213) {
    echo "\n   ✅ VERIFICACIÓN EXITOSA: Exactamente 213 artículos registrados\n";
} else {
    echo "\n   ⚠️  ADVERTENCIA: Se registraron {$catalogCount} artículos (se esperaban 213)\n";
}

echo "\n   Primeros 10 artículos (alfabéticamente):\n";
$samples = DB::table('stock_items')
    ->select('sku', 'quantity')
    ->orderBy('sku')
    ->limit(10)
    ->get();

foreach ($samples as $sample) {
    $qty = $sample->quantity == 0 ? '—' : $sample->quantity;
    echo "     - {$sample->sku}: {$qty}\n";
}

echo "\n   Últimos 5 artículos (alfabéticamente):\n";
$lastSamples = DB::table('stock_items')
    ->select('sku', 'quantity')
    ->orderBy('sku', 'desc')
    ->limit(5)
    ->get();

foreach (array_reverse($lastSamples->toArray()) as $sample) {
    $qty = $sample->quantity == 0 ? '—' : $sample->quantity;
    echo "     - {$sample->sku}: {$qty}\n";
}

echo "\n=== Inventario creado exitosamente ===\n";
