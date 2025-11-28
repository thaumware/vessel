-- =====================================================
-- VESSEL CATALOG - COMPLETE DATABASE SCHEMA
-- Version: 1.0.0
-- Generated: 2025-11-28
-- Database: MySQL 8.0+
-- =====================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- =====================================================
-- MODULE: LOCATIONS
-- Ubicaciones físicas y direcciones
-- =====================================================

-- Addresses (direcciones generales)
DROP TABLE IF EXISTS `locations_addresses`;
CREATE TABLE `locations_addresses` (
    `id` CHAR(36) NOT NULL PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `address_type` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `workspace_id` CHAR(36) NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    INDEX `idx_workspace` (`workspace_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Locations (ubicaciones con jerarquía)
DROP TABLE IF EXISTS `locations_locations`;
CREATE TABLE `locations_locations` (
    `id` CHAR(36) NOT NULL PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `type` VARCHAR(255) NOT NULL COMMENT 'warehouse, store, distribution_center, office, storage_unit',
    `address_id` CHAR(36) NULL,
    `parent_id` CHAR(36) NULL COMMENT 'Para jerarquía: storage_units dentro de warehouses',
    `workspace_id` CHAR(36) NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    INDEX `idx_type` (`type`),
    INDEX `idx_parent` (`parent_id`),
    INDEX `idx_workspace` (`workspace_id`),
    CONSTRAINT `fk_locations_parent` FOREIGN KEY (`parent_id`) 
        REFERENCES `locations_locations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- MODULE: TAXONOMY
-- Vocabularios y términos para categorización
-- =====================================================

-- Vocabularies (tipos de taxonomía: marcas, categorías, etc.)
DROP TABLE IF EXISTS `taxonomy_vocabularies`;
CREATE TABLE `taxonomy_vocabularies` (
    `id` CHAR(36) NOT NULL PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `workspace_id` CHAR(36) NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    UNIQUE KEY `uk_slug` (`slug`),
    INDEX `idx_workspace` (`workspace_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Terms (términos/etiquetas dentro de vocabularios)
DROP TABLE IF EXISTS `catalog_terms`;
CREATE TABLE `catalog_terms` (
    `id` CHAR(36) NOT NULL PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `slug` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `vocabulary_id` CHAR(36) NOT NULL,
    `workspace_id` CHAR(36) NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    UNIQUE KEY `uk_slug_vocabulary` (`slug`, `vocabulary_id`),
    INDEX `idx_vocabulary` (`vocabulary_id`),
    INDEX `idx_workspace` (`workspace_id`),
    CONSTRAINT `fk_terms_vocabulary` FOREIGN KEY (`vocabulary_id`) 
        REFERENCES `taxonomy_vocabularies` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Term Relations (jerarquía y relaciones entre términos)
DROP TABLE IF EXISTS `catalog_term_relations`;
CREATE TABLE `catalog_term_relations` (
    `id` CHAR(36) NOT NULL PRIMARY KEY,
    `from_term_id` CHAR(36) NOT NULL,
    `to_term_id` CHAR(36) NOT NULL,
    `relation_type` VARCHAR(50) NOT NULL DEFAULT 'parent' COMMENT 'parent, related, synonym',
    `depth` INT NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_from_term` (`from_term_id`),
    INDEX `idx_to_term` (`to_term_id`),
    UNIQUE KEY `uk_term_relation` (`from_term_id`, `to_term_id`, `relation_type`),
    CONSTRAINT `fk_term_rel_from` FOREIGN KEY (`from_term_id`) 
        REFERENCES `catalog_terms` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_term_rel_to` FOREIGN KEY (`to_term_id`) 
        REFERENCES `catalog_terms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- MODULE: ITEMS (CATALOG)
-- Productos del catálogo
-- =====================================================

-- Items (productos base)
DROP TABLE IF EXISTS `catalog_items`;
CREATE TABLE `catalog_items` (
    `id` CHAR(36) NOT NULL PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `uom_id` CHAR(36) NULL COMMENT 'FK a stock_units (unidad de medida)',
    `notes` TEXT NULL,
    `status` VARCHAR(50) NOT NULL DEFAULT 'active' COMMENT 'active, draft, archived',
    `workspace_id` CHAR(36) NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    INDEX `idx_status` (`status`),
    INDEX `idx_uom` (`uom_id`),
    INDEX `idx_workspace` (`workspace_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Item-Terms (relación M:M entre items y términos de taxonomía)
DROP TABLE IF EXISTS `catalog_item_terms`;
CREATE TABLE `catalog_item_terms` (
    `item_id` CHAR(36) NOT NULL,
    `term_id` CHAR(36) NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`item_id`, `term_id`),
    INDEX `idx_term` (`term_id`),
    CONSTRAINT `fk_item_terms_item` FOREIGN KEY (`item_id`) 
        REFERENCES `catalog_items` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_item_terms_term` FOREIGN KEY (`term_id`) 
        REFERENCES `catalog_terms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Item Identifiers (SKU, EAN, UPC, etc.)
DROP TABLE IF EXISTS `catalog_item_identifiers`;
CREATE TABLE `catalog_item_identifiers` (
    `id` CHAR(36) NOT NULL PRIMARY KEY,
    `item_id` CHAR(36) NOT NULL,
    `variant_id` CHAR(36) NULL COMMENT 'NULL = identificador del item base',
    `type` VARCHAR(50) NOT NULL COMMENT 'sku, ean, upc, gtin, mpn, supplier_code, custom',
    `value` VARCHAR(255) NOT NULL,
    `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_item` (`item_id`),
    INDEX `idx_variant` (`variant_id`),
    INDEX `idx_type_value` (`type`, `value`),
    UNIQUE KEY `uk_item_variant_type` (`item_id`, `variant_id`, `type`),
    CONSTRAINT `fk_identifiers_item` FOREIGN KEY (`item_id`) 
        REFERENCES `catalog_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- MODULE: STOCK
-- Gestión de inventario
-- =====================================================

-- Units of Measure (unidades de medida)
DROP TABLE IF EXISTS `stock_units`;
CREATE TABLE `stock_units` (
    `id` CHAR(36) NOT NULL PRIMARY KEY,
    `code` VARCHAR(50) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `workspace_id` CHAR(36) NULL,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    `deleted_at` DATETIME NULL,
    UNIQUE KEY `uk_code` (`code`),
    INDEX `idx_workspace` (`workspace_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Batches (lotes)
DROP TABLE IF EXISTS `stock_batches`;
CREATE TABLE `stock_batches` (
    `id` CHAR(36) NOT NULL PRIMARY KEY,
    `sku` VARCHAR(255) NOT NULL,
    `location_id` CHAR(36) NOT NULL,
    `quantity` INT NOT NULL DEFAULT 0,
    `lot_number` VARCHAR(255) NULL,
    `workspace_id` CHAR(36) NULL,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    `deleted_at` DATETIME NULL,
    INDEX `idx_sku` (`sku`),
    INDEX `idx_location` (`location_id`),
    INDEX `idx_sku_location` (`sku`, `location_id`),
    INDEX `idx_workspace` (`workspace_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Current Stock (stock actual consolidado)
DROP TABLE IF EXISTS `stock_current`;
CREATE TABLE `stock_current` (
    `id` CHAR(36) NOT NULL PRIMARY KEY,
    `sku` VARCHAR(255) NOT NULL,
    `location_id` CHAR(36) NOT NULL,
    `location_type` VARCHAR(100) NULL,
    `quantity` INT NOT NULL DEFAULT 0,
    `workspace_id` CHAR(36) NULL,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    `deleted_at` DATETIME NULL,
    INDEX `idx_sku` (`sku`),
    INDEX `idx_location` (`location_id`),
    INDEX `idx_location_type` (`location_type`),
    UNIQUE KEY `uk_sku_location` (`sku`, `location_id`, `location_type`),
    INDEX `idx_workspace` (`workspace_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock Movements (movimientos kardex)
DROP TABLE IF EXISTS `stock_movements`;
CREATE TABLE `stock_movements` (
    `id` CHAR(36) NOT NULL PRIMARY KEY,
    `movement_id` CHAR(36) NULL COMMENT 'ID externo / idempotency key',
    `sku` VARCHAR(255) NOT NULL,
    `location_from_id` CHAR(36) NULL,
    `location_from_type` VARCHAR(100) NULL,
    `location_to_id` CHAR(36) NULL,
    `location_to_type` VARCHAR(100) NULL,
    `quantity` INT NOT NULL,
    `balance_after` INT NULL COMMENT 'Saldo después del movimiento',
    `movement_type` VARCHAR(64) NULL COMMENT 'in, out, transfer, adjustment',
    `reference` VARCHAR(255) NULL COMMENT 'Referencia externa',
    `user_id` CHAR(36) NULL,
    `workspace_id` CHAR(36) NULL,
    `meta` JSON NULL,
    `created_at` DATETIME NULL,
    `processed_at` DATETIME NULL,
    `deleted_at` DATETIME NULL,
    INDEX `idx_movement_id` (`movement_id`),
    INDEX `idx_sku` (`sku`),
    INDEX `idx_location_from` (`location_from_id`),
    INDEX `idx_location_to` (`location_to_id`),
    INDEX `idx_type` (`movement_type`),
    INDEX `idx_workspace` (`workspace_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Stock Items (existencia física detallada)
DROP TABLE IF EXISTS `stock_items`;
CREATE TABLE `stock_items` (
    `id` CHAR(36) NOT NULL PRIMARY KEY,
    `sku` VARCHAR(255) NOT NULL,
    `catalog_item_id` VARCHAR(255) NULL COMMENT 'FK a catalog_items o sistema externo',
    `catalog_origin` VARCHAR(100) NULL DEFAULT 'internal' COMMENT 'internal, external_erp, etc.',
    `location_id` CHAR(36) NOT NULL,
    `location_type` VARCHAR(100) NOT NULL DEFAULT 'warehouse',
    `quantity` DECIMAL(15,4) NOT NULL DEFAULT 0,
    `reserved_quantity` DECIMAL(15,4) NOT NULL DEFAULT 0,
    `lot_number` VARCHAR(255) NULL,
    `expiration_date` DATE NULL,
    `serial_number` VARCHAR(255) NULL,
    `workspace_id` CHAR(36) NULL,
    `meta` JSON NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL,
    INDEX `idx_sku` (`sku`),
    INDEX `idx_location` (`location_id`),
    INDEX `idx_catalog_item` (`catalog_item_id`),
    INDEX `idx_catalog_origin` (`catalog_origin`),
    INDEX `idx_lot_number` (`lot_number`),
    INDEX `idx_expiration` (`expiration_date`),
    INDEX `idx_workspace` (`workspace_id`),
    UNIQUE KEY `uk_sku_location` (`sku`, `location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- INITIAL DATA: Units of Measure
-- =====================================================

INSERT INTO `stock_units` (`id`, `code`, `name`, `created_at`) VALUES
(UUID(), 'UN', 'Unidad', NOW()),
(UUID(), 'KG', 'Kilogramo', NOW()),
(UUID(), 'GR', 'Gramo', NOW()),
(UUID(), 'LT', 'Litro', NOW()),
(UUID(), 'ML', 'Mililitro', NOW()),
(UUID(), 'MT', 'Metro', NOW()),
(UUID(), 'CM', 'Centímetro', NOW()),
(UUID(), 'CJ', 'Caja', NOW()),
(UUID(), 'PQ', 'Paquete', NOW()),
(UUID(), 'BL', 'Bolsa', NOW()),
(UUID(), 'BT', 'Botella', NOW()),
(UUID(), 'RL', 'Rollo', NOW()),
(UUID(), 'PZ', 'Pieza', NOW()),
(UUID(), 'PR', 'Par', NOW()),
(UUID(), 'JG', 'Juego', NOW());

-- =====================================================
-- INITIAL DATA: Default Vocabularies
-- =====================================================

INSERT INTO `taxonomy_vocabularies` (`id`, `name`, `slug`, `description`, `created_at`) VALUES
(UUID(), 'Categorías', 'categories', 'Categorías de productos', NOW()),
(UUID(), 'Marcas', 'brands', 'Marcas de productos', NOW()),
(UUID(), 'Proveedores', 'suppliers', 'Proveedores de productos', NOW()),
(UUID(), 'Tags', 'tags', 'Etiquetas generales', NOW()),
(UUID(), 'Colores', 'colors', 'Colores disponibles', NOW()),
(UUID(), 'Tamaños', 'sizes', 'Tamaños disponibles', NOW());

SET FOREIGN_KEY_CHECKS = 1;

-- =====================================================
-- END OF SCHEMA
-- =====================================================
