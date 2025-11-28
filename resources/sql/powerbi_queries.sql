-- =====================================================
-- VESSEL CATALOG - QUERIES PARA POWER BI
-- =====================================================

-- =====================================================
-- 1. VISTA: Items con sus identificadores (SKU, EAN)
-- =====================================================
CREATE OR REPLACE VIEW vw_items_full AS
SELECT 
    i.id,
    i.name AS item_name,
    i.description,
    i.status,
    i.created_at,
    MAX(CASE WHEN ident.type = 'sku' THEN ident.value END) AS sku,
    MAX(CASE WHEN ident.type = 'ean' THEN ident.value END) AS ean,
    MAX(CASE WHEN ident.type = 'upc' THEN ident.value END) AS upc,
    MAX(CASE WHEN ident.type = 'supplier_code' THEN ident.value END) AS supplier_code
FROM catalog_items i
LEFT JOIN catalog_item_identifiers ident ON i.id = ident.item_id
GROUP BY i.id, i.name, i.description, i.status, i.created_at;

-- =====================================================
-- 2. VISTA: Items con categorías y marcas
-- =====================================================
CREATE OR REPLACE VIEW vw_items_taxonomy AS
SELECT 
    i.id,
    i.name AS item_name,
    i.status,
    GROUP_CONCAT(DISTINCT CASE WHEN v.slug = 'categories' THEN t.name END) AS categories,
    GROUP_CONCAT(DISTINCT CASE WHEN v.slug = 'brands' THEN t.name END) AS brand,
    GROUP_CONCAT(DISTINCT CASE WHEN v.slug = 'colors' THEN t.name END) AS colors,
    GROUP_CONCAT(DISTINCT CASE WHEN v.slug = 'sizes' THEN t.name END) AS sizes
FROM catalog_items i
LEFT JOIN catalog_item_terms it ON i.id = it.item_id
LEFT JOIN catalog_terms t ON it.term_id = t.id
LEFT JOIN taxonomy_vocabularies v ON t.vocabulary_id = v.id
GROUP BY i.id, i.name, i.status;

-- =====================================================
-- 3. VISTA: Stock por ubicación
-- =====================================================
CREATE OR REPLACE VIEW vw_stock_by_location AS
SELECT 
    l.id AS location_id,
    l.name AS location_name,
    l.type AS location_type,
    pl.name AS parent_location,
    COUNT(DISTINCT s.id) AS total_skus,
    SUM(s.quantity) AS total_quantity,
    SUM(s.reserved_quantity) AS total_reserved,
    SUM(s.quantity - s.reserved_quantity) AS available_quantity
FROM locations_locations l
LEFT JOIN locations_locations pl ON l.parent_id = pl.id
LEFT JOIN stock_items s ON l.id = s.location_id
GROUP BY l.id, l.name, l.type, pl.name;

-- =====================================================
-- 4. VISTA: Stock detallado con item info
-- =====================================================
CREATE OR REPLACE VIEW vw_stock_detail AS
SELECT 
    s.id AS stock_id,
    s.sku,
    i.name AS item_name,
    l.name AS location_name,
    l.type AS location_type,
    s.quantity,
    s.reserved_quantity,
    (s.quantity - s.reserved_quantity) AS available,
    s.lot_number,
    s.expiration_date,
    s.created_at
FROM stock_items s
LEFT JOIN catalog_items i ON s.catalog_item_id = i.id
LEFT JOIN locations_locations l ON s.location_id = l.id;

-- =====================================================
-- 5. VISTA: Movimientos de stock (kardex)
-- =====================================================
CREATE OR REPLACE VIEW vw_stock_movements AS
SELECT 
    m.id,
    m.sku,
    m.movement_type,
    m.quantity,
    m.balance_after,
    m.reference,
    lf.name AS location_from,
    lt.name AS location_to,
    m.created_at,
    m.processed_at
FROM stock_movements m
LEFT JOIN locations_locations lf ON m.location_from_id = lf.id
LEFT JOIN locations_locations lt ON m.location_to_id = lt.id;

-- =====================================================
-- 6. VISTA: Resumen de inventario por categoría
-- =====================================================
CREATE OR REPLACE VIEW vw_inventory_by_category AS
SELECT 
    COALESCE(t.name, 'Sin categoría') AS category,
    COUNT(DISTINCT i.id) AS total_items,
    COUNT(DISTINCT s.id) AS total_stock_records,
    SUM(s.quantity) AS total_quantity,
    SUM(s.reserved_quantity) AS reserved,
    AVG(s.quantity) AS avg_quantity_per_location
FROM catalog_items i
LEFT JOIN catalog_item_terms it ON i.id = it.item_id
LEFT JOIN catalog_terms t ON it.term_id = t.id
LEFT JOIN taxonomy_vocabularies v ON t.vocabulary_id = v.id AND v.slug = 'categories'
LEFT JOIN stock_items s ON i.id = s.catalog_item_id
GROUP BY t.name;

-- =====================================================
-- 7. VISTA: Items con bajo stock (alertas)
-- =====================================================
CREATE OR REPLACE VIEW vw_low_stock_alerts AS
SELECT 
    s.sku,
    i.name AS item_name,
    l.name AS location_name,
    s.quantity,
    s.reserved_quantity,
    (s.quantity - s.reserved_quantity) AS available,
    s.expiration_date,
    CASE 
        WHEN s.quantity = 0 THEN 'SIN STOCK'
        WHEN s.quantity < 10 THEN 'CRÍTICO'
        WHEN s.quantity < 50 THEN 'BAJO'
        ELSE 'NORMAL'
    END AS stock_status
FROM stock_items s
LEFT JOIN catalog_items i ON s.catalog_item_id = i.id
LEFT JOIN locations_locations l ON s.location_id = l.id
WHERE s.quantity < 50
ORDER BY s.quantity ASC;

-- =====================================================
-- 8. VISTA: Items próximos a vencer
-- =====================================================
CREATE OR REPLACE VIEW vw_expiring_soon AS
SELECT 
    s.sku,
    i.name AS item_name,
    l.name AS location_name,
    s.lot_number,
    s.quantity,
    s.expiration_date,
    DATEDIFF(s.expiration_date, CURDATE()) AS days_until_expiry,
    CASE 
        WHEN s.expiration_date < CURDATE() THEN 'VENCIDO'
        WHEN DATEDIFF(s.expiration_date, CURDATE()) <= 30 THEN 'VENCE EN 30 DÍAS'
        WHEN DATEDIFF(s.expiration_date, CURDATE()) <= 90 THEN 'VENCE EN 90 DÍAS'
        ELSE 'OK'
    END AS expiry_status
FROM stock_items s
LEFT JOIN catalog_items i ON s.catalog_item_id = i.id
LEFT JOIN locations_locations l ON s.location_id = l.id
WHERE s.expiration_date IS NOT NULL
ORDER BY s.expiration_date ASC;

-- =====================================================
-- 9. VISTA: Ubicaciones con jerarquía
-- =====================================================
CREATE OR REPLACE VIEW vw_locations_hierarchy AS
SELECT 
    l.id,
    l.name,
    l.type,
    l.description,
    p.name AS parent_name,
    p.type AS parent_type,
    CASE 
        WHEN l.parent_id IS NULL THEN l.name
        ELSE CONCAT(p.name, ' > ', l.name)
    END AS full_path
FROM locations_locations l
LEFT JOIN locations_locations p ON l.parent_id = p.id;

-- =====================================================
-- 10. QUERY: Dashboard KPIs
-- =====================================================
-- Usar esta query para obtener KPIs principales
SELECT 
    (SELECT COUNT(*) FROM catalog_items) AS total_items,
    (SELECT COUNT(*) FROM catalog_items WHERE status = 'active') AS active_items,
    (SELECT COUNT(*) FROM locations_locations) AS total_locations,
    (SELECT COUNT(*) FROM locations_locations WHERE type = 'warehouse') AS warehouses,
    (SELECT COUNT(*) FROM stock_items) AS stock_records,
    (SELECT SUM(quantity) FROM stock_items) AS total_stock_quantity,
    (SELECT SUM(reserved_quantity) FROM stock_items) AS total_reserved,
    (SELECT COUNT(*) FROM stock_items WHERE quantity = 0) AS out_of_stock,
    (SELECT COUNT(*) FROM stock_items WHERE quantity < 10 AND quantity > 0) AS low_stock,
    (SELECT COUNT(*) FROM stock_movements) AS total_movements;

-- =====================================================
-- INSTRUCCIONES POWER BI
-- =====================================================
-- 1. Ejecutar este script para crear las vistas
-- 2. En Power BI: Get Data > MySQL Database
-- 3. Server: localhost, Database: vessel_test
-- 4. Importar las vistas (vw_*) como tablas
-- 5. Crear relaciones entre tablas si es necesario
-- =====================================================
