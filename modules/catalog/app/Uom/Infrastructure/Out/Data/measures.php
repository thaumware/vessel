<?php

/**
 * Unidades de medida base para inventarios.
 * 
 * Categorías:
 * - generic: Unidades genéricas (piezas, unidades)
 * - mass: Masa/Peso (kg, g, lb, oz)
 * - volume: Volumen (l, ml, gal)
 * - length: Longitud (m, cm, ft, inch)
 * - area: Área (m², ft²)
 * - time: Tiempo (h, min, s)
 * - temperature: Temperatura (°C, °F, K)
 * - data: Almacenamiento digital (B, KB, MB, GB, TB)
 * - electrical: Eléctrico (V, A, W, Ω)
 * - concentration: Concentración (mol, ppm, %)
 * - packaging: Empaque (box, pack, pallet, roll)
 */

return [
    // ========================================
    // GENÉRICO (Conteo)
    // ========================================
    [
        'id' => 'uom-unit',
        'code' => 'unit',
        'name' => 'Unidad',
        'symbol' => 'u',
        'category' => 'generic',
        'is_base' => true,
        'description' => 'Unidad individual, pieza',
    ],
    [
        'id' => 'uom-pair',
        'code' => 'pair',
        'name' => 'Par',
        'symbol' => 'par',
        'category' => 'generic',
        'is_base' => false,
        'description' => 'Par de unidades (2 piezas)',
    ],
    [
        'id' => 'uom-dozen',
        'code' => 'dozen',
        'name' => 'Docena',
        'symbol' => 'dz',
        'category' => 'generic',
        'is_base' => false,
        'description' => 'Docena (12 unidades)',
    ],
    [
        'id' => 'uom-gross',
        'code' => 'gross',
        'name' => 'Gruesa',
        'symbol' => 'gro',
        'category' => 'generic',
        'is_base' => false,
        'description' => 'Gruesa (144 unidades)',
    ],
    [
        'id' => 'uom-hundred',
        'code' => 'hundred',
        'name' => 'Cien',
        'symbol' => 'C',
        'category' => 'generic',
        'is_base' => false,
        'description' => 'Cien unidades',
    ],
    [
        'id' => 'uom-thousand',
        'code' => 'thousand',
        'name' => 'Mil',
        'symbol' => 'M',
        'category' => 'generic',
        'is_base' => false,
        'description' => 'Mil unidades',
    ],

    // ========================================
    // MASA / PESO
    // ========================================
    [
        'id' => 'uom-kg',
        'code' => 'kg',
        'name' => 'Kilogramo',
        'symbol' => 'kg',
        'category' => 'mass',
        'is_base' => true,
        'description' => 'Unidad SI de masa',
    ],
    [
        'id' => 'uom-g',
        'code' => 'g',
        'name' => 'Gramo',
        'symbol' => 'g',
        'category' => 'mass',
        'is_base' => false,
        'description' => '1/1000 de kilogramo',
    ],
    [
        'id' => 'uom-mg',
        'code' => 'mg',
        'name' => 'Miligramo',
        'symbol' => 'mg',
        'category' => 'mass',
        'is_base' => false,
        'description' => '1/1000 de gramo',
    ],
    [
        'id' => 'uom-t',
        'code' => 't',
        'name' => 'Tonelada',
        'symbol' => 't',
        'category' => 'mass',
        'is_base' => false,
        'description' => '1000 kilogramos',
    ],
    [
        'id' => 'uom-lb',
        'code' => 'lb',
        'name' => 'Libra',
        'symbol' => 'lb',
        'category' => 'mass',
        'is_base' => false,
        'description' => 'Libra (sistema imperial)',
    ],
    [
        'id' => 'uom-oz',
        'code' => 'oz',
        'name' => 'Onza',
        'symbol' => 'oz',
        'category' => 'mass',
        'is_base' => false,
        'description' => 'Onza (sistema imperial)',
    ],

    // ========================================
    // VOLUMEN
    // ========================================
    [
        'id' => 'uom-l',
        'code' => 'l',
        'name' => 'Litro',
        'symbol' => 'L',
        'category' => 'volume',
        'is_base' => true,
        'description' => 'Unidad de volumen',
    ],
    [
        'id' => 'uom-ml',
        'code' => 'ml',
        'name' => 'Mililitro',
        'symbol' => 'mL',
        'category' => 'volume',
        'is_base' => false,
        'description' => '1/1000 de litro',
    ],
    [
        'id' => 'uom-m3',
        'code' => 'm3',
        'name' => 'Metro cúbico',
        'symbol' => 'm³',
        'category' => 'volume',
        'is_base' => false,
        'description' => '1000 litros',
    ],
    [
        'id' => 'uom-gal',
        'code' => 'gal',
        'name' => 'Galón',
        'symbol' => 'gal',
        'category' => 'volume',
        'is_base' => false,
        'description' => 'Galón US (3.785 litros)',
    ],
    [
        'id' => 'uom-fl-oz',
        'code' => 'fl_oz',
        'name' => 'Onza líquida',
        'symbol' => 'fl oz',
        'category' => 'volume',
        'is_base' => false,
        'description' => 'Onza líquida US',
    ],
    [
        'id' => 'uom-cup',
        'code' => 'cup',
        'name' => 'Taza',
        'symbol' => 'cup',
        'category' => 'volume',
        'is_base' => false,
        'description' => 'Taza US (236.588 ml)',
    ],

    // ========================================
    // LONGITUD
    // ========================================
    [
        'id' => 'uom-m',
        'code' => 'm',
        'name' => 'Metro',
        'symbol' => 'm',
        'category' => 'length',
        'is_base' => true,
        'description' => 'Unidad SI de longitud',
    ],
    [
        'id' => 'uom-cm',
        'code' => 'cm',
        'name' => 'Centímetro',
        'symbol' => 'cm',
        'category' => 'length',
        'is_base' => false,
        'description' => '1/100 de metro',
    ],
    [
        'id' => 'uom-mm',
        'code' => 'mm',
        'name' => 'Milímetro',
        'symbol' => 'mm',
        'category' => 'length',
        'is_base' => false,
        'description' => '1/1000 de metro',
    ],
    [
        'id' => 'uom-km',
        'code' => 'km',
        'name' => 'Kilómetro',
        'symbol' => 'km',
        'category' => 'length',
        'is_base' => false,
        'description' => '1000 metros',
    ],
    [
        'id' => 'uom-inch',
        'code' => 'inch',
        'name' => 'Pulgada',
        'symbol' => 'in',
        'category' => 'length',
        'is_base' => false,
        'description' => 'Pulgada (2.54 cm)',
    ],
    [
        'id' => 'uom-ft',
        'code' => 'ft',
        'name' => 'Pie',
        'symbol' => 'ft',
        'category' => 'length',
        'is_base' => false,
        'description' => 'Pie (12 pulgadas)',
    ],
    [
        'id' => 'uom-yd',
        'code' => 'yd',
        'name' => 'Yarda',
        'symbol' => 'yd',
        'category' => 'length',
        'is_base' => false,
        'description' => 'Yarda (3 pies)',
    ],

    // ========================================
    // ÁREA
    // ========================================
    [
        'id' => 'uom-m2',
        'code' => 'm2',
        'name' => 'Metro cuadrado',
        'symbol' => 'm²',
        'category' => 'area',
        'is_base' => true,
        'description' => 'Unidad SI de área',
    ],
    [
        'id' => 'uom-cm2',
        'code' => 'cm2',
        'name' => 'Centímetro cuadrado',
        'symbol' => 'cm²',
        'category' => 'area',
        'is_base' => false,
        'description' => '1/10000 de m²',
    ],
    [
        'id' => 'uom-ft2',
        'code' => 'ft2',
        'name' => 'Pie cuadrado',
        'symbol' => 'ft²',
        'category' => 'area',
        'is_base' => false,
        'description' => 'Pie cuadrado',
    ],
    [
        'id' => 'uom-ha',
        'code' => 'ha',
        'name' => 'Hectárea',
        'symbol' => 'ha',
        'category' => 'area',
        'is_base' => false,
        'description' => '10000 m²',
    ],

    // ========================================
    // TIEMPO
    // ========================================
    [
        'id' => 'uom-s',
        'code' => 's',
        'name' => 'Segundo',
        'symbol' => 's',
        'category' => 'time',
        'is_base' => true,
        'description' => 'Unidad SI de tiempo',
    ],
    [
        'id' => 'uom-min',
        'code' => 'min',
        'name' => 'Minuto',
        'symbol' => 'min',
        'category' => 'time',
        'is_base' => false,
        'description' => '60 segundos',
    ],
    [
        'id' => 'uom-h',
        'code' => 'h',
        'name' => 'Hora',
        'symbol' => 'h',
        'category' => 'time',
        'is_base' => false,
        'description' => '60 minutos',
    ],
    [
        'id' => 'uom-day',
        'code' => 'day',
        'name' => 'Día',
        'symbol' => 'd',
        'category' => 'time',
        'is_base' => false,
        'description' => '24 horas',
    ],

    // ========================================
    // TEMPERATURA
    // ========================================
    [
        'id' => 'uom-celsius',
        'code' => 'celsius',
        'name' => 'Grado Celsius',
        'symbol' => '°C',
        'category' => 'temperature',
        'is_base' => true,
        'description' => 'Escala Celsius',
    ],
    [
        'id' => 'uom-fahrenheit',
        'code' => 'fahrenheit',
        'name' => 'Grado Fahrenheit',
        'symbol' => '°F',
        'category' => 'temperature',
        'is_base' => false,
        'description' => 'Escala Fahrenheit',
    ],
    [
        'id' => 'uom-kelvin',
        'code' => 'kelvin',
        'name' => 'Kelvin',
        'symbol' => 'K',
        'category' => 'temperature',
        'is_base' => false,
        'description' => 'Escala absoluta SI',
    ],

    // ========================================
    // DATOS / ALMACENAMIENTO DIGITAL
    // ========================================
    [
        'id' => 'uom-byte',
        'code' => 'byte',
        'name' => 'Byte',
        'symbol' => 'B',
        'category' => 'data',
        'is_base' => true,
        'description' => 'Unidad base de datos',
    ],
    [
        'id' => 'uom-kb',
        'code' => 'kb',
        'name' => 'Kilobyte',
        'symbol' => 'KB',
        'category' => 'data',
        'is_base' => false,
        'description' => '1024 bytes',
    ],
    [
        'id' => 'uom-mb',
        'code' => 'mb',
        'name' => 'Megabyte',
        'symbol' => 'MB',
        'category' => 'data',
        'is_base' => false,
        'description' => '1024 KB',
    ],
    [
        'id' => 'uom-gb',
        'code' => 'gb',
        'name' => 'Gigabyte',
        'symbol' => 'GB',
        'category' => 'data',
        'is_base' => false,
        'description' => '1024 MB',
    ],
    [
        'id' => 'uom-tb',
        'code' => 'tb',
        'name' => 'Terabyte',
        'symbol' => 'TB',
        'category' => 'data',
        'is_base' => false,
        'description' => '1024 GB',
    ],

    // ========================================
    // ELÉCTRICO
    // ========================================
    [
        'id' => 'uom-v',
        'code' => 'v',
        'name' => 'Voltio',
        'symbol' => 'V',
        'category' => 'electrical',
        'is_base' => true,
        'description' => 'Unidad de voltaje',
    ],
    [
        'id' => 'uom-a',
        'code' => 'a',
        'name' => 'Amperio',
        'symbol' => 'A',
        'category' => 'electrical',
        'is_base' => true,
        'description' => 'Unidad de corriente',
    ],
    [
        'id' => 'uom-w',
        'code' => 'w',
        'name' => 'Vatio',
        'symbol' => 'W',
        'category' => 'electrical',
        'is_base' => false,
        'description' => 'Unidad de potencia',
    ],
    [
        'id' => 'uom-kw',
        'code' => 'kw',
        'name' => 'Kilovatio',
        'symbol' => 'kW',
        'category' => 'electrical',
        'is_base' => false,
        'description' => '1000 vatios',
    ],
    [
        'id' => 'uom-kwh',
        'code' => 'kwh',
        'name' => 'Kilovatio-hora',
        'symbol' => 'kWh',
        'category' => 'electrical',
        'is_base' => false,
        'description' => 'Energía consumida',
    ],
    [
        'id' => 'uom-ohm',
        'code' => 'ohm',
        'name' => 'Ohmio',
        'symbol' => 'Ω',
        'category' => 'electrical',
        'is_base' => false,
        'description' => 'Unidad de resistencia',
    ],
    [
        'id' => 'uom-mah',
        'code' => 'mah',
        'name' => 'Miliamperio-hora',
        'symbol' => 'mAh',
        'category' => 'electrical',
        'is_base' => false,
        'description' => 'Capacidad de batería',
    ],

    // ========================================
    // CONCENTRACIÓN / LABORATORIO
    // ========================================
    [
        'id' => 'uom-mol',
        'code' => 'mol',
        'name' => 'Mol',
        'symbol' => 'mol',
        'category' => 'concentration',
        'is_base' => true,
        'description' => 'Cantidad de sustancia',
    ],
    [
        'id' => 'uom-mmol',
        'code' => 'mmol',
        'name' => 'Milimol',
        'symbol' => 'mmol',
        'category' => 'concentration',
        'is_base' => false,
        'description' => '1/1000 mol',
    ],
    [
        'id' => 'uom-ppm',
        'code' => 'ppm',
        'name' => 'Partes por millón',
        'symbol' => 'ppm',
        'category' => 'concentration',
        'is_base' => false,
        'description' => 'Concentración muy baja',
    ],
    [
        'id' => 'uom-percent',
        'code' => 'percent',
        'name' => 'Porcentaje',
        'symbol' => '%',
        'category' => 'concentration',
        'is_base' => false,
        'description' => 'Partes por cien',
    ],
    [
        'id' => 'uom-ph',
        'code' => 'ph',
        'name' => 'pH',
        'symbol' => 'pH',
        'category' => 'concentration',
        'is_base' => false,
        'description' => 'Acidez/alcalinidad',
    ],

    // ========================================
    // EMPAQUE / LOGÍSTICA
    // ========================================
    [
        'id' => 'uom-box',
        'code' => 'box',
        'name' => 'Caja',
        'symbol' => 'box',
        'category' => 'packaging',
        'is_base' => false,
        'description' => 'Unidad de empaque: caja',
    ],
    [
        'id' => 'uom-pack',
        'code' => 'pack',
        'name' => 'Paquete',
        'symbol' => 'pk',
        'category' => 'packaging',
        'is_base' => false,
        'description' => 'Paquete de unidades',
    ],
    [
        'id' => 'uom-pallet',
        'code' => 'pallet',
        'name' => 'Pallet',
        'symbol' => 'plt',
        'category' => 'packaging',
        'is_base' => false,
        'description' => 'Pallet/tarima',
    ],
    [
        'id' => 'uom-roll',
        'code' => 'roll',
        'name' => 'Rollo',
        'symbol' => 'roll',
        'category' => 'packaging',
        'is_base' => false,
        'description' => 'Rollo de material',
    ],
    [
        'id' => 'uom-bag',
        'code' => 'bag',
        'name' => 'Bolsa',
        'symbol' => 'bag',
        'category' => 'packaging',
        'is_base' => false,
        'description' => 'Bolsa de empaque',
    ],
    [
        'id' => 'uom-bottle',
        'code' => 'bottle',
        'name' => 'Botella',
        'symbol' => 'btl',
        'category' => 'packaging',
        'is_base' => false,
        'description' => 'Botella/envase',
    ],
    [
        'id' => 'uom-can',
        'code' => 'can',
        'name' => 'Lata',
        'symbol' => 'can',
        'category' => 'packaging',
        'is_base' => false,
        'description' => 'Lata/bote',
    ],
    [
        'id' => 'uom-tube',
        'code' => 'tube',
        'name' => 'Tubo',
        'symbol' => 'tube',
        'category' => 'packaging',
        'is_base' => false,
        'description' => 'Tubo de ensayo o empaque',
    ],
    [
        'id' => 'uom-sheet',
        'code' => 'sheet',
        'name' => 'Hoja',
        'symbol' => 'sht',
        'category' => 'packaging',
        'is_base' => false,
        'description' => 'Hoja/lámina',
    ],
    [
        'id' => 'uom-ream',
        'code' => 'ream',
        'name' => 'Resma',
        'symbol' => 'rm',
        'category' => 'packaging',
        'is_base' => false,
        'description' => 'Resma (500 hojas)',
    ],
    [
        'id' => 'uom-set',
        'code' => 'set',
        'name' => 'Juego/Set',
        'symbol' => 'set',
        'category' => 'packaging',
        'is_base' => false,
        'description' => 'Juego completo de piezas',
    ],
    [
        'id' => 'uom-kit',
        'code' => 'kit',
        'name' => 'Kit',
        'symbol' => 'kit',
        'category' => 'packaging',
        'is_base' => false,
        'description' => 'Kit de componentes',
    ],
];
