<?php

/**
 * Conversiones entre unidades de medida.
 * 
 * Las conversiones se definen de forma unidireccional.
 * Para la conversión inversa, usar 1/factor o la operación contraria.
 * 
 * Operaciones:
 * - mul: multiplicar valor * factor
 * - div: dividir valor / factor
 * - add: sumar valor + factor (para temperatura)
 * - sub: restar valor - factor (para temperatura)
 */

return [
    // ========================================
    // MASA
    // ========================================
    ['id' => 'conv-kg-g', 'from_measure_id' => 'kg', 'to_measure_id' => 'g', 'factor' => 1000, 'operation' => 'mul'],
    ['id' => 'conv-g-kg', 'from_measure_id' => 'g', 'to_measure_id' => 'kg', 'factor' => 1000, 'operation' => 'div'],
    ['id' => 'conv-kg-mg', 'from_measure_id' => 'kg', 'to_measure_id' => 'mg', 'factor' => 1000000, 'operation' => 'mul'],
    ['id' => 'conv-mg-kg', 'from_measure_id' => 'mg', 'to_measure_id' => 'kg', 'factor' => 1000000, 'operation' => 'div'],
    ['id' => 'conv-g-mg', 'from_measure_id' => 'g', 'to_measure_id' => 'mg', 'factor' => 1000, 'operation' => 'mul'],
    ['id' => 'conv-mg-g', 'from_measure_id' => 'mg', 'to_measure_id' => 'g', 'factor' => 1000, 'operation' => 'div'],
    ['id' => 'conv-t-kg', 'from_measure_id' => 't', 'to_measure_id' => 'kg', 'factor' => 1000, 'operation' => 'mul'],
    ['id' => 'conv-kg-t', 'from_measure_id' => 'kg', 'to_measure_id' => 't', 'factor' => 1000, 'operation' => 'div'],
    ['id' => 'conv-kg-lb', 'from_measure_id' => 'kg', 'to_measure_id' => 'lb', 'factor' => 2.20462, 'operation' => 'mul'],
    ['id' => 'conv-lb-kg', 'from_measure_id' => 'lb', 'to_measure_id' => 'kg', 'factor' => 2.20462, 'operation' => 'div'],
    ['id' => 'conv-lb-oz', 'from_measure_id' => 'lb', 'to_measure_id' => 'oz', 'factor' => 16, 'operation' => 'mul'],
    ['id' => 'conv-oz-lb', 'from_measure_id' => 'oz', 'to_measure_id' => 'lb', 'factor' => 16, 'operation' => 'div'],
    ['id' => 'conv-oz-g', 'from_measure_id' => 'oz', 'to_measure_id' => 'g', 'factor' => 28.3495, 'operation' => 'mul'],
    ['id' => 'conv-g-oz', 'from_measure_id' => 'g', 'to_measure_id' => 'oz', 'factor' => 28.3495, 'operation' => 'div'],

    // ========================================
    // VOLUMEN
    // ========================================
    ['id' => 'conv-l-ml', 'from_measure_id' => 'l', 'to_measure_id' => 'ml', 'factor' => 1000, 'operation' => 'mul'],
    ['id' => 'conv-ml-l', 'from_measure_id' => 'ml', 'to_measure_id' => 'l', 'factor' => 1000, 'operation' => 'div'],
    ['id' => 'conv-m3-l', 'from_measure_id' => 'm3', 'to_measure_id' => 'l', 'factor' => 1000, 'operation' => 'mul'],
    ['id' => 'conv-l-m3', 'from_measure_id' => 'l', 'to_measure_id' => 'm3', 'factor' => 1000, 'operation' => 'div'],
    ['id' => 'conv-gal-l', 'from_measure_id' => 'gal', 'to_measure_id' => 'l', 'factor' => 3.78541, 'operation' => 'mul'],
    ['id' => 'conv-l-gal', 'from_measure_id' => 'l', 'to_measure_id' => 'gal', 'factor' => 3.78541, 'operation' => 'div'],
    ['id' => 'conv-gal-fl_oz', 'from_measure_id' => 'gal', 'to_measure_id' => 'fl_oz', 'factor' => 128, 'operation' => 'mul'],
    ['id' => 'conv-fl_oz-gal', 'from_measure_id' => 'fl_oz', 'to_measure_id' => 'gal', 'factor' => 128, 'operation' => 'div'],
    ['id' => 'conv-fl_oz-ml', 'from_measure_id' => 'fl_oz', 'to_measure_id' => 'ml', 'factor' => 29.5735, 'operation' => 'mul'],
    ['id' => 'conv-ml-fl_oz', 'from_measure_id' => 'ml', 'to_measure_id' => 'fl_oz', 'factor' => 29.5735, 'operation' => 'div'],
    ['id' => 'conv-cup-ml', 'from_measure_id' => 'cup', 'to_measure_id' => 'ml', 'factor' => 236.588, 'operation' => 'mul'],
    ['id' => 'conv-ml-cup', 'from_measure_id' => 'ml', 'to_measure_id' => 'cup', 'factor' => 236.588, 'operation' => 'div'],

    // ========================================
    // LONGITUD
    // ========================================
    ['id' => 'conv-m-cm', 'from_measure_id' => 'm', 'to_measure_id' => 'cm', 'factor' => 100, 'operation' => 'mul'],
    ['id' => 'conv-cm-m', 'from_measure_id' => 'cm', 'to_measure_id' => 'm', 'factor' => 100, 'operation' => 'div'],
    ['id' => 'conv-m-mm', 'from_measure_id' => 'm', 'to_measure_id' => 'mm', 'factor' => 1000, 'operation' => 'mul'],
    ['id' => 'conv-mm-m', 'from_measure_id' => 'mm', 'to_measure_id' => 'm', 'factor' => 1000, 'operation' => 'div'],
    ['id' => 'conv-cm-mm', 'from_measure_id' => 'cm', 'to_measure_id' => 'mm', 'factor' => 10, 'operation' => 'mul'],
    ['id' => 'conv-mm-cm', 'from_measure_id' => 'mm', 'to_measure_id' => 'cm', 'factor' => 10, 'operation' => 'div'],
    ['id' => 'conv-km-m', 'from_measure_id' => 'km', 'to_measure_id' => 'm', 'factor' => 1000, 'operation' => 'mul'],
    ['id' => 'conv-m-km', 'from_measure_id' => 'm', 'to_measure_id' => 'km', 'factor' => 1000, 'operation' => 'div'],
    ['id' => 'conv-inch-cm', 'from_measure_id' => 'inch', 'to_measure_id' => 'cm', 'factor' => 2.54, 'operation' => 'mul'],
    ['id' => 'conv-cm-inch', 'from_measure_id' => 'cm', 'to_measure_id' => 'inch', 'factor' => 2.54, 'operation' => 'div'],
    ['id' => 'conv-m-inch', 'from_measure_id' => 'm', 'to_measure_id' => 'inch', 'factor' => 39.3701, 'operation' => 'mul'],
    ['id' => 'conv-inch-m', 'from_measure_id' => 'inch', 'to_measure_id' => 'm', 'factor' => 39.3701, 'operation' => 'div'],
    ['id' => 'conv-ft-inch', 'from_measure_id' => 'ft', 'to_measure_id' => 'inch', 'factor' => 12, 'operation' => 'mul'],
    ['id' => 'conv-inch-ft', 'from_measure_id' => 'inch', 'to_measure_id' => 'ft', 'factor' => 12, 'operation' => 'div'],
    ['id' => 'conv-ft-m', 'from_measure_id' => 'ft', 'to_measure_id' => 'm', 'factor' => 0.3048, 'operation' => 'mul'],
    ['id' => 'conv-m-ft', 'from_measure_id' => 'm', 'to_measure_id' => 'ft', 'factor' => 0.3048, 'operation' => 'div'],
    ['id' => 'conv-yd-ft', 'from_measure_id' => 'yd', 'to_measure_id' => 'ft', 'factor' => 3, 'operation' => 'mul'],
    ['id' => 'conv-ft-yd', 'from_measure_id' => 'ft', 'to_measure_id' => 'yd', 'factor' => 3, 'operation' => 'div'],
    ['id' => 'conv-yd-m', 'from_measure_id' => 'yd', 'to_measure_id' => 'm', 'factor' => 0.9144, 'operation' => 'mul'],
    ['id' => 'conv-m-yd', 'from_measure_id' => 'm', 'to_measure_id' => 'yd', 'factor' => 0.9144, 'operation' => 'div'],

    // ========================================
    // ÁREA
    // ========================================
    ['id' => 'conv-m2-cm2', 'from_measure_id' => 'm2', 'to_measure_id' => 'cm2', 'factor' => 10000, 'operation' => 'mul'],
    ['id' => 'conv-cm2-m2', 'from_measure_id' => 'cm2', 'to_measure_id' => 'm2', 'factor' => 10000, 'operation' => 'div'],
    ['id' => 'conv-m2-ft2', 'from_measure_id' => 'm2', 'to_measure_id' => 'ft2', 'factor' => 10.7639, 'operation' => 'mul'],
    ['id' => 'conv-ft2-m2', 'from_measure_id' => 'ft2', 'to_measure_id' => 'm2', 'factor' => 10.7639, 'operation' => 'div'],
    ['id' => 'conv-ha-m2', 'from_measure_id' => 'ha', 'to_measure_id' => 'm2', 'factor' => 10000, 'operation' => 'mul'],
    ['id' => 'conv-m2-ha', 'from_measure_id' => 'm2', 'to_measure_id' => 'ha', 'factor' => 10000, 'operation' => 'div'],

    // ========================================
    // TIEMPO
    // ========================================
    ['id' => 'conv-min-s', 'from_measure_id' => 'min', 'to_measure_id' => 's', 'factor' => 60, 'operation' => 'mul'],
    ['id' => 'conv-s-min', 'from_measure_id' => 's', 'to_measure_id' => 'min', 'factor' => 60, 'operation' => 'div'],
    ['id' => 'conv-h-min', 'from_measure_id' => 'h', 'to_measure_id' => 'min', 'factor' => 60, 'operation' => 'mul'],
    ['id' => 'conv-min-h', 'from_measure_id' => 'min', 'to_measure_id' => 'h', 'factor' => 60, 'operation' => 'div'],
    ['id' => 'conv-h-s', 'from_measure_id' => 'h', 'to_measure_id' => 's', 'factor' => 3600, 'operation' => 'mul'],
    ['id' => 'conv-s-h', 'from_measure_id' => 's', 'to_measure_id' => 'h', 'factor' => 3600, 'operation' => 'div'],
    ['id' => 'conv-day-h', 'from_measure_id' => 'day', 'to_measure_id' => 'h', 'factor' => 24, 'operation' => 'mul'],
    ['id' => 'conv-h-day', 'from_measure_id' => 'h', 'to_measure_id' => 'day', 'factor' => 24, 'operation' => 'div'],

    // ========================================
    // TEMPERATURA (conversiones especiales)
    // ========================================
    // Celsius a Kelvin: K = C + 273.15
    ['id' => 'conv-celsius-kelvin', 'from_measure_id' => 'celsius', 'to_measure_id' => 'kelvin', 'factor' => 273.15, 'operation' => 'add'],
    // Kelvin a Celsius: C = K - 273.15
    ['id' => 'conv-kelvin-celsius', 'from_measure_id' => 'kelvin', 'to_measure_id' => 'celsius', 'factor' => 273.15, 'operation' => 'sub'],
    // Nota: Fahrenheit requiere conversión más compleja (F = C * 9/5 + 32), se maneja en código

    // ========================================
    // DATOS / ALMACENAMIENTO
    // ========================================
    ['id' => 'conv-kb-byte', 'from_measure_id' => 'kb', 'to_measure_id' => 'byte', 'factor' => 1024, 'operation' => 'mul'],
    ['id' => 'conv-byte-kb', 'from_measure_id' => 'byte', 'to_measure_id' => 'kb', 'factor' => 1024, 'operation' => 'div'],
    ['id' => 'conv-mb-kb', 'from_measure_id' => 'mb', 'to_measure_id' => 'kb', 'factor' => 1024, 'operation' => 'mul'],
    ['id' => 'conv-kb-mb', 'from_measure_id' => 'kb', 'to_measure_id' => 'mb', 'factor' => 1024, 'operation' => 'div'],
    ['id' => 'conv-gb-mb', 'from_measure_id' => 'gb', 'to_measure_id' => 'mb', 'factor' => 1024, 'operation' => 'mul'],
    ['id' => 'conv-mb-gb', 'from_measure_id' => 'mb', 'to_measure_id' => 'gb', 'factor' => 1024, 'operation' => 'div'],
    ['id' => 'conv-tb-gb', 'from_measure_id' => 'tb', 'to_measure_id' => 'gb', 'factor' => 1024, 'operation' => 'mul'],
    ['id' => 'conv-gb-tb', 'from_measure_id' => 'gb', 'to_measure_id' => 'tb', 'factor' => 1024, 'operation' => 'div'],

    // ========================================
    // ELÉCTRICO
    // ========================================
    ['id' => 'conv-kw-w', 'from_measure_id' => 'kw', 'to_measure_id' => 'w', 'factor' => 1000, 'operation' => 'mul'],
    ['id' => 'conv-w-kw', 'from_measure_id' => 'w', 'to_measure_id' => 'kw', 'factor' => 1000, 'operation' => 'div'],

    // ========================================
    // GENÉRICO (Conteo)
    // ========================================
    ['id' => 'conv-pair-unit', 'from_measure_id' => 'pair', 'to_measure_id' => 'unit', 'factor' => 2, 'operation' => 'mul'],
    ['id' => 'conv-unit-pair', 'from_measure_id' => 'unit', 'to_measure_id' => 'pair', 'factor' => 2, 'operation' => 'div'],
    ['id' => 'conv-dozen-unit', 'from_measure_id' => 'dozen', 'to_measure_id' => 'unit', 'factor' => 12, 'operation' => 'mul'],
    ['id' => 'conv-unit-dozen', 'from_measure_id' => 'unit', 'to_measure_id' => 'dozen', 'factor' => 12, 'operation' => 'div'],
    ['id' => 'conv-gross-unit', 'from_measure_id' => 'gross', 'to_measure_id' => 'unit', 'factor' => 144, 'operation' => 'mul'],
    ['id' => 'conv-unit-gross', 'from_measure_id' => 'unit', 'to_measure_id' => 'gross', 'factor' => 144, 'operation' => 'div'],
    ['id' => 'conv-gross-dozen', 'from_measure_id' => 'gross', 'to_measure_id' => 'dozen', 'factor' => 12, 'operation' => 'mul'],
    ['id' => 'conv-dozen-gross', 'from_measure_id' => 'dozen', 'to_measure_id' => 'gross', 'factor' => 12, 'operation' => 'div'],
    ['id' => 'conv-hundred-unit', 'from_measure_id' => 'hundred', 'to_measure_id' => 'unit', 'factor' => 100, 'operation' => 'mul'],
    ['id' => 'conv-unit-hundred', 'from_measure_id' => 'unit', 'to_measure_id' => 'hundred', 'factor' => 100, 'operation' => 'div'],
    ['id' => 'conv-thousand-unit', 'from_measure_id' => 'thousand', 'to_measure_id' => 'unit', 'factor' => 1000, 'operation' => 'mul'],
    ['id' => 'conv-unit-thousand', 'from_measure_id' => 'unit', 'to_measure_id' => 'thousand', 'factor' => 1000, 'operation' => 'div'],

    // ========================================
    // CONCENTRACIÓN
    // ========================================
    ['id' => 'conv-mol-mmol', 'from_measure_id' => 'mol', 'to_measure_id' => 'mmol', 'factor' => 1000, 'operation' => 'mul'],
    ['id' => 'conv-mmol-mol', 'from_measure_id' => 'mmol', 'to_measure_id' => 'mol', 'factor' => 1000, 'operation' => 'div'],
    ['id' => 'conv-percent-ppm', 'from_measure_id' => 'percent', 'to_measure_id' => 'ppm', 'factor' => 10000, 'operation' => 'mul'],
    ['id' => 'conv-ppm-percent', 'from_measure_id' => 'ppm', 'to_measure_id' => 'percent', 'factor' => 10000, 'operation' => 'div'],

    // ========================================
    // EMPAQUE (Resma)
    // ========================================
    ['id' => 'conv-ream-sheet', 'from_measure_id' => 'ream', 'to_measure_id' => 'sheet', 'factor' => 500, 'operation' => 'mul'],
    ['id' => 'conv-sheet-ream', 'from_measure_id' => 'sheet', 'to_measure_id' => 'ream', 'factor' => 500, 'operation' => 'div'],
];