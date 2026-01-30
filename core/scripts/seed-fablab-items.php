<?php
/**
 * Script para cargar items del FabLab
 * Ejecutar: docker compose exec -T core php scripts/seed-fablab-items.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Symfony\Component\Uid\Uuid;

$items = [
    "Revistas Carrefooth",
    "Manuak de Logo genérico",
    "Manual artillery x1",
    "AF voca Hat",
    "botiquin bo sur",
    "Kit Circuit Maker (miyaare estampados)",
    "Diplomas 'Olimpiadas 2023'",
    "Caja organizadora Truper (vacías)",
    "Yo Stanley 8 detorniladores",
    "Ventilador USB",
    "Kit Lentes UN Shervecon",
    "Luz LED U L100",
    "Placa Arduino Nano",
    "Act teria",
    "Adaptador AC/DC 10 V Uplilight",
    "Pilas AFALTO Triple A",
    "Pilas PHILLO Doble AA",
    "Pilas Maxceller Triple AAA",
    "Pilas Maxceller Doble AA",
    "Carcasa Raspberry Pi Rojo y Blanco",
    "Ventilador doble con disipador de calor",
    "Sensor de calibración",
    "Cable USB-C",
    "Cable USB mini B",
    "impresora esp 6200 mod",
    "motor servo 9g",
    "aky mic keyboard",
    "Raspberry Pi 1",
    "Raspberry Pi 4",
    "Raspberry Pizero 2",
    "Plaza Zoom Core",
    "Conector de entrada de fuente de alimentación ROJO GRIS",
    "Conector de entrada de fuente de alimentación para PCB NEGRO (m)",
    "Battery Charger AIWA",
    "Resin joyas y manualidades RESMART CRISTAL",
    "Endurecedor joyas y manualidades Resmart Cristal",
    "50homas adhesivo para impresión 3D Print a Lot",
    "Pegamientos para talacho de fieltro",
    "Modulo de mando",
    "Módulo de matriz de punto LED 8x8 controlador MAX 7219",
    "Módulo de cámara raspberry pi",
    "Módulo brújula de chino",
    "Driver Motor paso a paso módulo 2PH8530A",
    "Pantalla LCD 1602",
    "Alorudi ESP8266 1MBs",
    "cable ribbon 40 pines (raspberry)",
    "Zonas térmicas",
    "Extensor triángulo",
    "Lavapiés triángulo Trisolar",
    "Capas de cargador de Meta 2",
    "Raspberry Pi Camera v2",
    "Cable batería de 9 pines",
    "Alianza magnético set",
    "módulo comparamiento aislante",
    "jumper macho macho",
    "Módulo de cámara Raspberry Pi V2",
    "Botón pulsador iluminado LED Blanco",
    "Cable plano flexible (FFC) LARGO 15 pines",
    "Cable plano flexible (FFC) CORTO 21 pines",
    "Enchufe de pared de 12 volts",
    "Fuentes de alimentación IO",
    "Fuente de alimentación Samsung",
    "Fuentes de alimentación de Delta Electronics",
    "mouse inalambrico eq",
    "mouse inalambrico BQ zunnit",
    "teclado BQ",
    "Control de juegos",
    "controladores Asus",
    "Cargador Asus de PC 19v 1.75A",
    "webcam",
    "Cable Ethernet (RJ45)",
    "Cable uba a micro usb",
    "Cable usb a mini usb",
    "Aleagator Ring",
    "Chec proxymar Detall",
    "Hangups GiBit",
    "Cámara Gafet 4",
    "Mouse genios Wireless",
    "Samsung Galaxy j4+",
    "mouse genios con cable",
    "mouse Logitech con cable",
    "teclado genios",
    "teclado HP",
    "energyster fx",
    "Soporte de cámara",
    "raspberry pi cargador",
    "Mini water pump Low Noise 120L",
    "Otras válvulas solenoide",
    "Servo motor",
    "Sensor de Temperatura",
    "Sensor de Temperatura y Humedad",
    "Sensor capacitivo de humedad suelo",
    "TecJaldo USB",
    "Headphone Hb",
    "Sondas sensor de temperatura Ds18b20",
    "Exeluitor de altavoz",
    "Cable de válvula de pulso",
    "Ampolete DIAR",
    "Pizarrón",
    "Portafolio",
    "Razer fury tower Pro S000",
    "Accesorio Rc Hoorn 3 cables servo",
    "Pantalla ACER W573",
    "Placa controladora LCD TTL LVDS",
    "Pantalla GL750",
    "Placa controladora LCD VGA 24V con retroailuminación",
    "Placa controladora LCD TTL LVDS a VGA con retroiluminación HDR",
    "Cable plano de datos de serial 50 pines",
    "Fuente de alimentación AC/DC adaptador intercambiable 1",
    "Potencimetro BQ",
    "Amplificador",
    "Adaptador acer para celula de carga HO711",
    "Cable micro HDMI a Raspberry",
    "Resistor Metano A7S",
    "Capa de mascarillas 50 piezas",
    "Capa de guantes",
    "Visa ring ZK",
    "Conector de dos pines Adhesivo PVC",
    "Kit placa de extensión GPIO para Raspberry",
    "Botón pulsador iluminado LED Verde",
    "Botón pulsador iluminado LED Azul",
    "cable 4 pines",
    "cable 6 pines",
    "Módulo de entrada de dos pines Adhesivo PVC",
    "Módulo de entrada de alimentación C14 con interruptor bas",
    "Regulador de voltaje con pantalla",
    "Módulo ESP32",
    "arduino pro mini",
    "Display 7 segmentos, 1 digito",
    "Display 7 segmentos, 4 digitos",
    "Cargador solar OGI 1.0w",
    "Adaptador RGB LED pin",
    "Motor DC 1.5-6v 2 piezas",
    "Tipo A MicroPenta-USB1ke 4 pines (impresoral)",
    "Imán",
    "Resina para soldadura thin reforçado con acero",
    "USB ESP32WROOM",
    "Ventilador Soits impresora 3D",
    "Bolsas de fillers",
    "Atajercas de fieres alien",
    "bqReader",
    "Paquetes de q. post it 500 hojas",
    "Paquetes de q. post it 400 hojas",
    "Tarjetas de post it micro",
    "Cargador Xiaomi 5v con puerto usb",
    "Capas de acción tipo metalico varias",
    "Capa de acción marillos con cierra",
    "Libro de asistencia",
    "Pack de etiquetas",
    "tapones auditivos",
    "Iaminas de silicon color verde",
    "Notas adhesivas amarillo 100b Foshan",
    "Ropa de ligas 8x color 500 gr",
    "Caja de marcadores de pizarra rojo 12u",
    "Pack de barras de silicon para pistola de silicon 10 u",
    "Portador de papeles con imán",
    "Capa de lapices 8lc negro 50 u",
    "Capa de lapices 8lc azul 50 u",
    "Tarjetas finca red de navidad",
    "Software SW-X1 Artiliery",
    "LED cu cabeceado",
    "Adaptador Alriba a cabezono 3/32",
    "Adaptador",
    "Adaptador multiplicate Mini DisplayPort a HDMIVGA/DVI Din",
    "Cable alimentación de impresora 3D",
    "Cables de laptop Dell",
    "Cable conectores Solo 55130562 F2 12v 0.8a",
    "Cable Acer adaptator modelo PA1650",
    "cable bus Q25D-02 de fuente de alimentación 360 w",
    "Botella agua mineral"
];

echo "=== Cargando " . count($items) . " items del FabLab ===\n\n";

$created = 0;
$skipped = 0;
$errors = 0;

foreach ($items as $name) {
    $name = trim($name);
    if (empty($name)) continue;

    // Verificar si ya existe
    $exists = DB::table('catalog_items')
        ->whereNull('deleted_at')
        ->where('name', $name)
        ->exists();

    if ($exists) {
        echo "⏭ Ya existe: {$name}\n";
        $skipped++;
        continue;
    }

    try {
        $id = Uuid::v4()->toRfc4122();
        $now = now();

        DB::table('catalog_items')->insert([
            'id' => $id,
            'name' => $name,
            'description' => null,
            'uom_id' => null,
            'notes' => null,
            'status' => 'active',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        echo "✓ Creado: {$name}\n";
        $created++;
    } catch (\Exception $e) {
        echo "✗ Error en '{$name}': " . $e->getMessage() . "\n";
        $errors++;
    }
}

echo "\n=== Resumen ===\n";
echo "Creados: {$created}\n";
echo "Ya existían: {$skipped}\n";
echo "Errores: {$errors}\n";
echo "Total procesados: " . ($created + $skipped + $errors) . "\n";
