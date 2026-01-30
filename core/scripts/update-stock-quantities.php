<?php
/**
 * Script para actualizar el stock de todos los artículos
 */

require_once __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

echo "=== Actualizando Stock de Artículos ===\n\n";

// Lista completa de artículos con cantidades
$items = [
    'AC Adapter 5V' => 7,
    'Accesorio Rc Hosim 3 cables servo' => 2,
    'Adaptador AC/DC 10 V UpBright' => 10,
    'Adaptador AC/DC 240v' => 1,
    'Adaptador HDMI a VGA' => 1,
    'Adaptador multipuerto Mini DisplayPort a HDMI/VGA/DVI Dinon' => 3,
    'Alcohol Isopropílico' => 1,
    'Alargador Rittig' => 1,
    'Alargadores' => 4,
    'Alargadores inteligentes' => 3,
    'Amplificador de celda de carga SparkFun - HX711 SEN-1879' => 1,
    'Amplificador para célula de carga HX711' => 3,
    'Anotador simple' => 3,
    'Ampolleto DAIR' => 1,
    'Archivador Torre' => 3,
    'Audífonos con cable Samsung' => 2,
    'AIY microphone hat' => 14,
    'AIY Vision bonnet' => 14,
    'AIY voice hat' => 14,
    'Battery Charger AA//AAA' => 3,
    'Batería Power Maxx SB1290-F2 12v 9,0Ah' => 1,
    'Bolsas para basura 10 u.' => 4,
    'Bolsita componentes arduino' => 1,
    'Bolsita con clips de cintura' => 6,
    'Bolsita de llaves allen' => 1,
    'Bolsitas bio sur' => 58,
    'Bolsitas de tornillos' => 15,
    'Botón BQ' => 13,
    'Botón pulsador iluminado LED Amarillo' => 11,
    'Botón pulsador iluminado LED Blanco' => 14,
    'Botón pulsador iluminado LED Rojo' => 1,
    'Botón pulsador iluminado LED Verde' => 2,
    'Bremen' => 1,
    'Brocha' => 1,
    'Cable adaptador USB a conector DC' => 1,
    'Cable Banana Banana' => 9,
    'Cable con conectores jack' => 1,
    'Cable conector de 10 pines' => 13,
    'Cable de batería estándar (color negro)' => 1,
    'Cable de batería rojo' => 1,
    'Cable de poder' => 18,
    'Cable de poder de PC entrada americana' => 0, // Sin especificar
    'Cable Ethernet (RJ45)' => 2,
    'Cable flex 20 pines' => 1,
    'Cable HDMI' => 1,
    'Cable HDMI para elíptica Octane Fitness' => 4,
    'Cable micro HDMI a HDMI Raspberry' => 2,
    'Cable OTG' => 5,
    'Cable plano de datos de señal 10 pines' => 1,
    'Cable plano flexible (FFC) CORTO 21 pines' => 12,
    'Cable plano flexible (FFC) LARGO 15 pines' => 12,
    'Cable ribbon 40 pines (raspberry)' => 4,
    'Cable USB mini B' => 9,
    'Cable USB-A mini B Cisco' => 3,
    'Cable USB a mini USB' => 10,
    'Cable VGA Video Graphics Array' => 2,
    'Caja de accoclips metálicos 50 u aprox' => 7,
    'Caja de cargador de Meta Quest 2' => 2,
    'Caja de corchetes 5000 unidades' => 2,
    'Caja de lápices Bic negro 50 u.' => 1,
    'Caja de lápices Bic rojos 50 u.' => 2,
    'Caja de marcadores de pizarra rojo 12u.' => 1,
    'Caja de marcadores de pizarra verde 12u.' => 1,
    'Caja de mascarillas 50 piezas' => 1,
    'Caja organizadora Truper (vacías)' => 9,
    'Calculadora Casio' => 1,
    'Cajas de cargador de Meta Quest 2' => 2,
    'Cargador de computador LG 19v' => 3,
    'Cargador Xiaomi 5v con doble puerto' => 1,
    'Cargador Samsung original' => 2,
    'Cargadores Asus de PC 19v 1,75A' => 35,
    'Carcasa Raspberry Pi Rojo y Blanco' => 1,
    'Carpetas en general' => 144,
    'Carrete alargador HALUX amarillo y negro' => 1,
    'Celular Samsung Galaxy A21s' => 1,
    'Cepillo de alambre' => 6,
    'Chip prepago Entel' => 11,
    'Computadora torre 8CG9105HYR' => 1,
    'Computadores Asus' => 34,
    'Conector de dos pines Adhesivo PVC' => 1,
    'Conector de entrada de fuente de alimentación NEGRO (negativo)' => 87,
    'Conector de entrada de fuente de alimentación ROJO (positivo)' => 87,
    'Controlador de motor' => 1,
    'Cinta de papel adhesiva Masking Tape' => 8,
    'Cables de poder' => 18,
    'Diafragma de válvula de pulso' => 30,
    'Diplomas Olimpiadas 2023' => 9,
    'Display 7 segmentos, 1 dígito' => 3,
    'Display 7 segmentos, 4 dígitos' => 2,
    'Driver Motor paso a paso modelo 2PH85309A' => 3,
    'Electrodo para acero al carbono 3/32\'\'' => 12,
    'Endurecedor joyas y manualidades Resinart Cristal' => 1,
    'Entrenador didáctico PLC SIEMENS logo!' => 1,
    'Escobillón rojo' => 5,
    'Esmalte sintético Tricolor' => 1,
    'Estuche SW-X1 Artillery' => 3,
    'Fuente de alimentación AC/DC adaptador intercambiable 12v' => 1,
    'Fuente de alimentación Delta Electronics' => 1,
    'Fuente de alimentación LG' => 4,
    'Fuente de alimentación Micro USB Zowi' => 5,
    'Fuente de alimentación Samsung' => 1,
    'Fuente de poder de 12 volts' => 3,
    'Galaxy Note 10s' => 4,
    'HDMI premium' => 4,
    'Headlights Spotlight' => 13,
    'Imán' => 1,
    'Jockey Biosur' => 2,
    'jumper macho macho' => 1,
    'Kit Cricut Maker (máquina estampados)' => 1,
    'Kit de placa de extensión GPIO para Raspberry' => 3,
    'Kit lentes VR Shinecon' => 6,
    'Kit Stanley 6 destornilladores' => 6,
    'Lápices Camptech' => 960,
    'LED cableado' => 13,
    'Libro de asistencia' => 2,
    'Luz LED LD-160' => 1,
    'Manual artillery x1' => 6,
    'Manual de Lego genérico' => 36,
    'Meta Quest 2' => 3,
    'Micro servo Tower Pro SG90' => 2,
    'Mini servomotor BQ Zumkit' => 24,
    'Mini válvula solenoide' => 2,
    'Mini water pump Low Noise 120L' => 2,
    'Modulo ESP32' => 1,
    'Modulo ESP8266MOD' => 5,
    'Modulo RGB LED 3 pines' => 2,
    'Modulo segmento LED' => 4,
    'Modulo sensor de temperatura y humedad DHT11' => 6,
    'Modulo NFC' => 12,
    'Modulo cámara Raspberry Pi V2' => 2,
    'Modulo cámara Raspberry Pi v2' => 7,
    'Módulo de entrada de alimentación C14 con interruptor y fusible' => 1,
    'Módulo de matriz de punto LED 8x8 MAX7219' => 3,
    'Módulo de sensor de medio puente de tensión de resistencia' => 4,
    'Monitor ELO' => 1,
    'Mouse Asus' => 40,
    'Mouse Genius con cable' => 4,
    'Mouse Genius Wireless' => 3,
    'Mouse Philco' => 5,
    'Notas adhesivas amarillo 100 hojas Fultons' => 40,
    'Pack de barras de silicona para pistola 10 u.' => 1,
    'Pack de cinta de papel adhesiva Helian 10 u.' => 4,
    'Pack de opalinas' => 1,
    'Pantalla LCD 1602' => 4,
    'Pantalla OLED 10 in' => 1,
    'Parlante' => 15,
    'Partes de servo pequeño' => 24,
    'Paquetes de 6 u. Post-it 500 hojas' => 11,
    'Paquetes de 6 u. Post-it 400 hojas' => 1,
    'Pegamento para tubos de PVC' => 1,
    'Pilas Macrotel Triple AAA' => 16,
    'Pilas PHILCO Doble AA' => 15,
    'Pilas PHILCO Triple AAA' => 133,
    'Placa Arduino Nano' => 9,
    'Placa controladora LCD TTL LVDS' => 1,
    'Placa controladora LCD VGA 2AV HDMI' => 1,
    'Placa Zoom Core' => 13,
    'Portacredenciales' => 825,
    'Portapilas' => 14,
    'Potenciómetro 500K' => 6,
    'Potenciómetro BQ' => 13,
    'Protoboard' => 4,
    'Raspberry Pi 0' => 14,
    'Raspberry Pi 3' => 6,
    'Raspberry Pi 4' => 1,
    'Raspberry Pi Camera v2' => 6,
    'Raspberry cargador' => 2,
    'Resina de joyas y manualidades Resinart Cristal' => 1,
    'Resina para soldadura fría reforzado con acero' => 1,
    'Revistas Camptech' => 62,
    'Robot Zowi' => 3,
    'Rollo de stickers Fab Lab' => 1,
    'Rótula de bola QZSD-02 aluminio 360°' => 1,
    'Samsung Galaxy J4' => 2,
    'Samsung Galaxy J4+' => 3,
    'Sensor capacitivo de humedad del suelo' => 8,
    'Sensor de calibración' => 1,
    'Sensor de luz BQ' => 14,
    'Sensor de temperatura' => 5,
    'Sensor de temperatura y humedad' => 1,
    'Sensor infrarrojo BQ' => 28,
    'Sensor ultrasonido BQ' => 13,
    'Servomotor SpringRC' => 7,
    'Soporte de cámara' => 1,
    'Soquete Mec' => 1,
    'Soldadura fría' => 1,
    'Spray adhesivo impresión 3D Print a Lot' => 1,
    'Tacos de Post-it 500 hojas' => 4,
    'Tacos de Post-it 400 hojas' => 2,
    'Taladro percutor' => 1,
    'Tarjeteros Inacap red de exalumnos' => 7,
    'Teclado Genius' => 9,
    'Teclado HP' => 1,
    'Teclado Lenovo' => 3,
    'Teclado Philco' => 6,
    'Teclado Ultra' => 1,
    'Tijeras' => 2,
    'USB tipo A / Mini-USB 4 pines (impresora)' => 7,
    'Ventilador doble con disipador de calor' => 0, // Sin especificar
    'Ventilador soplador 5015 impresora 3D' => 3,
    'Ventilador USB' => 4,
    'Warning light' => 1,
    'Zowi board BQ' => 1,
    'Zumbador BQ' => 13,
];

// Obtener ubicación Camptech
$location = DB::table('locations_locations')->where('name', 'Camptech')->first();
if (!$location) {
    echo "❌ Error: No se encontró la ubicación Camptech\n";
    exit(1);
}
$locationId = $location->id;
echo "Ubicación Camptech: {$locationId}\n\n";

$updated = 0;
$created = 0;
$movementsCreated = 0;
$notFound = [];

foreach ($items as $nombre => $cantidad) {
    // Buscar el stock item por nombre
    $stockItem = DB::table('stock_items')->where('sku', $nombre)->first();
    
    if (!$stockItem) {
        $notFound[] = $nombre;
        continue;
    }
    
    $currentQty = $stockItem->quantity;
    
    // Si la cantidad es diferente, actualizar
    if ($currentQty != $cantidad) {
        // Actualizar cantidad en stock_items
        DB::table('stock_items')
            ->where('id', $stockItem->id)
            ->update([
                'quantity' => $cantidad,
                'updated_at' => now(),
            ]);
        
        // Si la cantidad actual es 0 y la nueva es > 0, crear movimiento de entrada
        if ($currentQty == 0 && $cantidad > 0) {
            $movementId = (string) Str::uuid();
            DB::table('stock_movements')->insert([
                'id' => $movementId,
                'sku' => $nombre,
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
                'created_at' => now(),
                'updated_at' => now(),
                'processed_at' => now(),
            ]);
            $movementsCreated++;
        }
        
        $updated++;
        echo "✓ {$nombre}: {$currentQty} → {$cantidad}\n";
    }
}

echo "\n=== Resumen ===\n";
echo "Artículos actualizados: {$updated}\n";
echo "Movimientos de entrada creados: {$movementsCreated}\n";

if (!empty($notFound)) {
    echo "\n⚠️  Artículos no encontrados (" . count($notFound) . "):\n";
    foreach ($notFound as $name) {
        echo "  - {$name}\n";
    }
}

// Verificar totales
$totalStock = DB::table('stock_items')->sum('quantity');
$itemsConStock = DB::table('stock_items')->where('quantity', '>', 0)->count();
$itemsSinStock = DB::table('stock_items')->where('quantity', '=', 0)->count();

echo "\n=== Estado Final ===\n";
echo "Items con stock: {$itemsConStock}\n";
echo "Items sin stock: {$itemsSinStock}\n";
echo "Stock total: {$totalStock} unidades\n";

echo "\n=== Completado ===\n";
