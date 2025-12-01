<?php

/**
 * Categorías/Familias de unidades de medida.
 * 
 * Cada categoría agrupa unidades relacionadas que pueden
 * convertirse entre sí.
 */

return [
    [
        'id' => 'uom-cat-generic',
        'code' => 'generic',
        'name' => 'Genérico',
        'description' => 'Unidades de conteo genérico (piezas, unidades, docenas)',
        'icon' => 'hash',
        'sort_order' => 1,
    ],
    [
        'id' => 'uom-cat-mass',
        'code' => 'mass',
        'name' => 'Masa',
        'description' => 'Unidades de masa y peso (kilogramos, gramos, libras)',
        'icon' => 'weight',
        'sort_order' => 2,
    ],
    [
        'id' => 'uom-cat-volume',
        'code' => 'volume',
        'name' => 'Volumen',
        'description' => 'Unidades de volumen y capacidad (litros, galones, mililitros)',
        'icon' => 'droplet',
        'sort_order' => 3,
    ],
    [
        'id' => 'uom-cat-length',
        'code' => 'length',
        'name' => 'Longitud',
        'description' => 'Unidades de longitud y distancia (metros, pulgadas, pies)',
        'icon' => 'ruler',
        'sort_order' => 4,
    ],
    [
        'id' => 'uom-cat-area',
        'code' => 'area',
        'name' => 'Área',
        'description' => 'Unidades de superficie (metros cuadrados, hectáreas)',
        'icon' => 'square',
        'sort_order' => 5,
    ],
    [
        'id' => 'uom-cat-time',
        'code' => 'time',
        'name' => 'Tiempo',
        'description' => 'Unidades de tiempo (segundos, minutos, horas, días)',
        'icon' => 'clock',
        'sort_order' => 6,
    ],
    [
        'id' => 'uom-cat-temperature',
        'code' => 'temperature',
        'name' => 'Temperatura',
        'description' => 'Escalas de temperatura (Celsius, Fahrenheit, Kelvin)',
        'icon' => 'thermometer',
        'sort_order' => 7,
    ],
    [
        'id' => 'uom-cat-data',
        'code' => 'data',
        'name' => 'Datos',
        'description' => 'Unidades de almacenamiento digital (bytes, megabytes, gigabytes)',
        'icon' => 'hard-drive',
        'sort_order' => 8,
    ],
    [
        'id' => 'uom-cat-electrical',
        'code' => 'electrical',
        'name' => 'Eléctrico',
        'description' => 'Unidades eléctricas (voltios, amperios, vatios, ohmios)',
        'icon' => 'zap',
        'sort_order' => 9,
    ],
    [
        'id' => 'uom-cat-concentration',
        'code' => 'concentration',
        'name' => 'Concentración',
        'description' => 'Unidades de concentración y laboratorio (mol, ppm, pH)',
        'icon' => 'flask',
        'sort_order' => 10,
    ],
    [
        'id' => 'uom-cat-packaging',
        'code' => 'packaging',
        'name' => 'Empaque',
        'description' => 'Unidades de empaque y logística (cajas, pallets, bolsas)',
        'icon' => 'package',
        'sort_order' => 11,
    ],
];
