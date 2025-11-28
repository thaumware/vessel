#!/usr/bin/env python3
"""
VESSEL CATALOG - Data Faker
Genera datos de prueba y los inserta directamente en la base de datos

Uso:
    python faker_data.py --items 1000 --locations 50 --stock 5000
    python faker_data.py --items 100000 --direct  # Inserta directo en BD
    python faker_data.py --output sql  # Solo genera archivo SQL

Requisitos:
    pip install faker mysql-connector-python

Configuración BD (variables de entorno o argumentos):
    --host localhost --port 3306 --user root --password secret --database vessel_test
"""

import argparse
import json
import random
import uuid
import os
from datetime import datetime, timedelta

try:
    import mysql.connector
    HAS_MYSQL = True
except ImportError:
    HAS_MYSQL = False

from faker import Faker

fake = Faker(['es_ES', 'en_US'])

# =====================================================
# CONFIGURACIÓN
# =====================================================

LOCATION_TYPES = ['warehouse', 'store', 'distribution_center', 'office']
STORAGE_UNIT_TYPES = ['storage_unit']
ITEM_STATUS = ['active', 'active', 'active', 'draft', 'archived']  # Weighted
IDENTIFIER_TYPES = ['sku', 'ean', 'upc', 'gtin', 'mpn', 'supplier_code']
MOVEMENT_TYPES = ['in', 'out', 'transfer', 'adjustment']
UOM_CODES = ['UN', 'KG', 'GR', 'LT', 'ML', 'MT', 'CM', 'CJ', 'PQ', 'BL', 'BT', 'RL', 'PZ', 'PR', 'JG']

# Categorías de productos realistas
PRODUCT_CATEGORIES = [
    'Electrónica', 'Computación', 'Hogar', 'Cocina', 'Jardín',
    'Deportes', 'Ropa', 'Calzado', 'Juguetes', 'Libros',
    'Oficina', 'Papelería', 'Herramientas', 'Automotriz', 'Mascotas',
    'Salud', 'Belleza', 'Alimentos', 'Bebidas', 'Limpieza'
]

BRANDS = [
    'Samsung', 'Apple', 'Sony', 'LG', 'HP', 'Dell', 'Lenovo', 'Asus',
    'Nike', 'Adidas', 'Puma', 'Reebok', 'Under Armour',
    'Bosch', 'Makita', 'DeWalt', 'Stanley', 'Black & Decker',
    'Nestlé', 'Coca-Cola', 'PepsiCo', 'Unilever', 'P&G',
    'Generic', 'OEM', 'NoName', 'Import', 'Local'
]

COLORS = ['Rojo', 'Azul', 'Verde', 'Negro', 'Blanco', 'Gris', 'Amarillo', 'Naranja', 'Rosa', 'Morado']
SIZES = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'Único', '32', '34', '36', '38', '40', '42']

# =====================================================
# GENERADORES
# =====================================================

def generate_uuid():
    return str(uuid.uuid4())

def generate_slug(name):
    return name.lower().replace(' ', '-').replace('á', 'a').replace('é', 'e').replace('í', 'i').replace('ó', 'o').replace('ú', 'u').replace('ñ', 'n')

def generate_sku():
    prefix = random.choice(['SKU', 'PRD', 'ART', 'ITM', ''])
    number = fake.random_number(digits=random.randint(4, 8), fix_len=True)
    return f"{prefix}{number}"

def generate_ean():
    return fake.ean13()

def generate_upc():
    return fake.ean(length=12)

def now():
    return datetime.now().strftime('%Y-%m-%d %H:%M:%S')

def random_date(start_days=-365, end_days=365):
    delta = timedelta(days=random.randint(start_days, end_days))
    return (datetime.now() + delta).strftime('%Y-%m-%d')

# =====================================================
# DATA GENERATORS
# =====================================================

class VesselFaker:
    def __init__(self, workspace_id=None):
        self.workspace_id = workspace_id or generate_uuid()
        self.vocabularies = {}
        self.terms = {}
        self.locations = []
        self.items = []
        self.stock_items = []
        
    def generate_vocabularies(self):
        """Genera vocabularios estándar"""
        vocabs = [
            ('Categorías', 'categories', 'Categorías de productos'),
            ('Marcas', 'brands', 'Marcas de productos'),
            ('Proveedores', 'suppliers', 'Proveedores'),
            ('Tags', 'tags', 'Etiquetas'),
            ('Colores', 'colors', 'Colores'),
            ('Tamaños', 'sizes', 'Tamaños'),
        ]
        
        result = []
        for name, slug, desc in vocabs:
            vocab_id = generate_uuid()
            self.vocabularies[slug] = vocab_id
            result.append({
                'id': vocab_id,
                'name': name,
                'slug': slug,
                'description': desc,
                'workspace_id': self.workspace_id,
                'created_at': now(),
                'updated_at': now(),
            })
        return result
    
    def generate_terms(self, count_per_vocab=20):
        """Genera términos para cada vocabulario"""
        result = []
        
        # Categorías
        for cat in PRODUCT_CATEGORIES[:count_per_vocab]:
            term_id = generate_uuid()
            self.terms.setdefault('categories', []).append(term_id)
            result.append({
                'id': term_id,
                'name': cat,
                'slug': generate_slug(cat),
                'description': f'Categoría: {cat}',
                'vocabulary_id': self.vocabularies['categories'],
                'workspace_id': self.workspace_id,
                'created_at': now(),
                'updated_at': now(),
            })
        
        # Marcas
        for brand in BRANDS[:count_per_vocab]:
            term_id = generate_uuid()
            self.terms.setdefault('brands', []).append(term_id)
            result.append({
                'id': term_id,
                'name': brand,
                'slug': generate_slug(brand),
                'description': f'Marca: {brand}',
                'vocabulary_id': self.vocabularies['brands'],
                'workspace_id': self.workspace_id,
                'created_at': now(),
                'updated_at': now(),
            })
        
        # Colores
        for color in COLORS:
            term_id = generate_uuid()
            self.terms.setdefault('colors', []).append(term_id)
            result.append({
                'id': term_id,
                'name': color,
                'slug': generate_slug(color),
                'description': f'Color: {color}',
                'vocabulary_id': self.vocabularies['colors'],
                'workspace_id': self.workspace_id,
                'created_at': now(),
                'updated_at': now(),
            })
        
        # Tamaños
        for size in SIZES:
            term_id = generate_uuid()
            self.terms.setdefault('sizes', []).append(term_id)
            result.append({
                'id': term_id,
                'name': size,
                'slug': generate_slug(size),
                'description': f'Tamaño: {size}',
                'vocabulary_id': self.vocabularies['sizes'],
                'workspace_id': self.workspace_id,
                'created_at': now(),
                'updated_at': now(),
            })
        
        return result
    
    def generate_locations(self, count=50, storage_units_per_location=5):
        """Genera ubicaciones con jerarquía"""
        result = []
        
        # Generar ubicaciones principales (warehouses, stores, etc.)
        main_locations = []
        for i in range(count):
            loc_type = random.choice(LOCATION_TYPES)
            loc_id = generate_uuid()
            
            if loc_type == 'warehouse':
                name = f"Bodega {fake.city()}"
            elif loc_type == 'store':
                name = f"Tienda {fake.street_name()}"
            elif loc_type == 'distribution_center':
                name = f"Centro Distribución {fake.city()}"
            else:
                name = f"Oficina {fake.company()}"
            
            location = {
                'id': loc_id,
                'name': name,
                'description': fake.sentence(),
                'type': loc_type,
                'address_id': None,
                'parent_id': None,
                'workspace_id': self.workspace_id,
                'created_at': now(),
                'updated_at': now(),
            }
            
            result.append(location)
            main_locations.append(loc_id)
            self.locations.append(loc_id)
        
        # Generar storage units dentro de warehouses
        warehouses = [l for l in result if l['type'] == 'warehouse']
        for warehouse in warehouses:
            for j in range(random.randint(2, storage_units_per_location)):
                unit_id = generate_uuid()
                unit_type = random.choice(['Estante', 'Rack', 'Cajón', 'Zona', 'Pasillo', 'Bin'])
                
                storage_unit = {
                    'id': unit_id,
                    'name': f"{unit_type} {fake.random_letter().upper()}{random.randint(1, 99)}",
                    'description': f'{unit_type} en {warehouse["name"]}',
                    'type': 'storage_unit',
                    'address_id': None,
                    'parent_id': warehouse['id'],
                    'workspace_id': self.workspace_id,
                    'created_at': now(),
                    'updated_at': now(),
                }
                
                result.append(storage_unit)
                self.locations.append(unit_id)
        
        return result
    
    def generate_items(self, count=1000):
        """Genera items del catálogo"""
        result = []
        
        for i in range(count):
            item_id = generate_uuid()
            
            # Nombre de producto realista
            category = random.choice(PRODUCT_CATEGORIES)
            adjective = fake.word()
            product_type = fake.word()
            name = f"{adjective.title()} {product_type.title()} - {category}"
            
            item = {
                'id': item_id,
                'name': name[:255],
                'description': fake.paragraph(nb_sentences=3) if random.random() > 0.3 else None,
                'uom_id': None,  # Se puede vincular después
                'notes': fake.sentence() if random.random() > 0.7 else None,
                'status': random.choice(ITEM_STATUS),
                'workspace_id': self.workspace_id,
                'created_at': now(),
                'updated_at': now(),
            }
            
            result.append(item)
            self.items.append(item_id)
        
        return result
    
    def generate_item_identifiers(self, items):
        """Genera identificadores (SKU, EAN, etc.) para items"""
        result = []
        
        for item in items:
            # SKU principal (siempre)
            result.append({
                'id': generate_uuid(),
                'item_id': item['id'],
                'variant_id': None,
                'type': 'sku',
                'value': generate_sku(),
                'is_primary': 1,
                'created_at': now(),
                'updated_at': now(),
            })
            
            # EAN (70% probabilidad)
            if random.random() > 0.3:
                result.append({
                    'id': generate_uuid(),
                    'item_id': item['id'],
                    'variant_id': None,
                    'type': 'ean',
                    'value': generate_ean(),
                    'is_primary': 0,
                    'created_at': now(),
                    'updated_at': now(),
                })
            
            # Código proveedor (40% probabilidad)
            if random.random() > 0.6:
                result.append({
                    'id': generate_uuid(),
                    'item_id': item['id'],
                    'variant_id': None,
                    'type': 'supplier_code',
                    'value': f"SUP-{fake.random_number(digits=6, fix_len=True)}",
                    'is_primary': 0,
                    'created_at': now(),
                    'updated_at': now(),
                })
        
        return result
    
    def generate_item_terms(self, items):
        """Asocia términos a items (categorías, marcas, etc.)"""
        result = []
        
        for item in items:
            # 1-2 categorías
            if self.terms.get('categories'):
                for term_id in random.sample(self.terms['categories'], min(random.randint(1, 2), len(self.terms['categories']))):
                    result.append({
                        'item_id': item['id'],
                        'term_id': term_id,
                        'created_at': now(),
                        'updated_at': now(),
                    })
            
            # 1 marca (80% probabilidad)
            if self.terms.get('brands') and random.random() > 0.2:
                result.append({
                    'item_id': item['id'],
                    'term_id': random.choice(self.terms['brands']),
                    'created_at': now(),
                    'updated_at': now(),
                })
        
        return result
    
    def generate_stock_items(self, count=5000):
        """Genera stock en ubicaciones"""
        result = []
        
        if not self.items or not self.locations:
            raise ValueError("Debe generar items y locations primero")
        
        used_combinations = set()
        
        for i in range(count):
            # Evitar duplicados de SKU + location
            attempts = 0
            while attempts < 100:
                item_id = random.choice(self.items)
                location_id = random.choice(self.locations)
                sku = generate_sku()
                key = (sku, location_id)
                
                if key not in used_combinations:
                    used_combinations.add(key)
                    break
                attempts += 1
            
            if attempts >= 100:
                continue
            
            quantity = random.randint(0, 1000)
            reserved = random.randint(0, min(quantity, 100)) if quantity > 0 else 0
            
            stock_item = {
                'id': generate_uuid(),
                'sku': sku,
                'catalog_item_id': item_id,
                'catalog_origin': 'internal',
                'location_id': location_id,
                'location_type': 'warehouse',
                'quantity': quantity,
                'reserved_quantity': reserved,
                'lot_number': f"LOT-{fake.random_number(digits=6, fix_len=True)}" if random.random() > 0.5 else None,
                'expiration_date': random_date(30, 730) if random.random() > 0.7 else None,
                'serial_number': None,
                'workspace_id': self.workspace_id,
                'meta': None,
                'created_at': now(),
                'updated_at': now(),
            }
            
            result.append(stock_item)
            self.stock_items.append(stock_item['id'])
        
        return result
    
    def generate_stock_movements(self, count=10000):
        """Genera movimientos de stock históricos"""
        result = []
        
        for i in range(count):
            movement_type = random.choice(MOVEMENT_TYPES)
            sku = generate_sku()
            
            location_from = random.choice(self.locations) if movement_type in ['out', 'transfer'] else None
            location_to = random.choice(self.locations) if movement_type in ['in', 'transfer'] else None
            
            movement = {
                'id': generate_uuid(),
                'movement_id': generate_uuid() if random.random() > 0.5 else None,
                'sku': sku,
                'location_from_id': location_from,
                'location_from_type': 'warehouse' if location_from else None,
                'location_to_id': location_to,
                'location_to_type': 'warehouse' if location_to else None,
                'quantity': random.randint(1, 500),
                'balance_after': random.randint(0, 2000),
                'movement_type': movement_type,
                'reference': f"REF-{fake.random_number(digits=8, fix_len=True)}" if random.random() > 0.4 else None,
                'user_id': generate_uuid() if random.random() > 0.3 else None,
                'workspace_id': self.workspace_id,
                'meta': None,
                'created_at': random_date(-365, 0) + ' ' + fake.time(),
                'processed_at': random_date(-365, 0) + ' ' + fake.time(),
            }
            
            result.append(movement)
        
        return result


# =====================================================
# OUTPUT FORMATTERS
# =====================================================

def to_sql_insert(table, data):
    """Convierte datos a sentencias INSERT SQL"""
    if not data:
        return ""
    
    columns = data[0].keys()
    col_str = ', '.join(f'`{c}`' for c in columns)
    
    values = []
    for row in data:
        row_values = []
        for col in columns:
            val = row[col]
            if val is None:
                row_values.append('NULL')
            elif isinstance(val, (int, float)):
                row_values.append(str(val))
            else:
                escaped = str(val).replace("'", "''")
                row_values.append(f"'{escaped}'")
        values.append(f"({', '.join(row_values)})")
    
    # Batch inserts (1000 por batch)
    result = []
    batch_size = 1000
    for i in range(0, len(values), batch_size):
        batch = values[i:i + batch_size]
        result.append(f"INSERT INTO `{table}` ({col_str}) VALUES\n" + ',\n'.join(batch) + ';')
    
    return '\n\n'.join(result)


def batch_insert(cursor, table, data, batch_size=1000):
    """Inserta datos en batches directamente en la BD"""
    if not data:
        return 0
    
    columns = list(data[0].keys())
    col_str = ', '.join(f'`{c}`' for c in columns)
    placeholders = ', '.join(['%s'] * len(columns))
    
    sql = f"INSERT INTO `{table}` ({col_str}) VALUES ({placeholders})"
    
    total = 0
    for i in range(0, len(data), batch_size):
        batch = data[i:i + batch_size]
        values = [tuple(row[col] for col in columns) for row in batch]
        cursor.executemany(sql, values)
        total += len(batch)
        
        if total % 10000 == 0:
            print(f"   ... {total:,} registros insertados")
    
    return total


def insert_direct_to_db(faker, host, port, user, password, database,
                        items_count=1000, locations_count=50, stock_count=3000, 
                        movements_count=5000, clean_tables=False, only_movements=False):
    """Inserta datos directamente en MySQL"""
    
    print(f"🔌 Conectando a {host}:{port}/{database}...")
    
    conn = mysql.connector.connect(
        host=host,
        port=port,
        user=user,
        password=password,
        database=database,
        charset='utf8mb4'
    )
    cursor = conn.cursor()
    
    # Desactivar FK checks para velocidad
    cursor.execute("SET FOREIGN_KEY_CHECKS = 0")
    cursor.execute("SET UNIQUE_CHECKS = 0")
    cursor.execute("SET AUTOCOMMIT = 0")
    
    try:
        # Modo solo movements - usa locations existentes de la BD
        if only_movements:
            print("📍 Cargando locations existentes de la BD...")
            cursor.execute("SELECT id FROM locations_locations")
            faker.locations = [row[0] for row in cursor.fetchall()]
            print(f"   Encontradas {len(faker.locations)} ubicaciones")
            
            if not faker.locations:
                raise ValueError("No hay ubicaciones en la BD. Ejecuta primero sin --only-movements")
            
            print(f"Generando {movements_count:,} movimientos...")
            movements = faker.generate_stock_movements(count=movements_count)
            
            print(f"\n📥 Insertando {len(movements):,} movimientos...")
            batch_insert(cursor, 'stock_movements', movements)
            conn.commit()
            
            cursor.execute("SET FOREIGN_KEY_CHECKS = 1")
            cursor.execute("SET UNIQUE_CHECKS = 1")
            
            print(f"\n✅ Insertados {len(movements):,} movimientos!")
            return
        
        if clean_tables:
            print("🧹 Limpiando tablas...")
            tables_to_clean = [
                'stock_movements', 'stock_items', 'stock_current', 'stock_batches',
                'catalog_item_terms', 'catalog_item_identifiers', 'catalog_items',
                'catalog_term_relations', 'catalog_terms',
                'locations_locations', 'locations_addresses',
                'taxonomy_vocabularies'
            ]
            for table in tables_to_clean:
                try:
                    cursor.execute(f"TRUNCATE TABLE `{table}`")
                    print(f"   ✓ {table}")
                except Exception as e:
                    print(f"   ⚠ {table}: {e}")
            conn.commit()
        
        print("Generando vocabularios...")
        vocabularies = faker.generate_vocabularies()
        
        print("Generando términos...")
        terms = faker.generate_terms(count_per_vocab=25)
        
        print(f"Generando {locations_count:,} ubicaciones...")
        locations = faker.generate_locations(count=locations_count, storage_units_per_location=5)
        
        print(f"Generando {items_count:,} items...")
        items = faker.generate_items(count=items_count)
        
        print("Generando identificadores...")
        identifiers = faker.generate_item_identifiers(items)
        
        print("Generando item-terms...")
        item_terms = faker.generate_item_terms(items)
        
        print(f"Generando {stock_count:,} stock items...")
        stock_items = faker.generate_stock_items(count=stock_count)
        
        movements = []
        if movements_count > 0:
            print(f"Generando {movements_count:,} movimientos...")
            movements = faker.generate_stock_movements(count=movements_count)
        
        # Insertar en orden de dependencias
        print("\n📥 Insertando en base de datos...")
        
        print(f"  → taxonomy_vocabularies ({len(vocabularies)})")
        batch_insert(cursor, 'taxonomy_vocabularies', vocabularies)
        conn.commit()
        
        print(f"  → catalog_terms ({len(terms)})")
        batch_insert(cursor, 'catalog_terms', terms)
        conn.commit()
        
        # Primero locations root, luego children
        locations_root = [l for l in locations if l['parent_id'] is None]
        locations_children = [l for l in locations if l['parent_id'] is not None]
        
        print(f"  → locations_locations root ({len(locations_root)})")
        batch_insert(cursor, 'locations_locations', locations_root)
        conn.commit()
        
        if locations_children:
            print(f"  → locations_locations children ({len(locations_children)})")
            batch_insert(cursor, 'locations_locations', locations_children)
            conn.commit()
        
        print(f"  → catalog_items ({len(items):,})")
        batch_insert(cursor, 'catalog_items', items)
        conn.commit()
        
        print(f"  → catalog_item_identifiers ({len(identifiers):,})")
        batch_insert(cursor, 'catalog_item_identifiers', identifiers)
        conn.commit()
        
        print(f"  → catalog_item_terms ({len(item_terms):,})")
        batch_insert(cursor, 'catalog_item_terms', item_terms)
        conn.commit()
        
        print(f"  → stock_items ({len(stock_items):,})")
        batch_insert(cursor, 'stock_items', stock_items)
        conn.commit()
        
        if movements:
            print(f"  → stock_movements ({len(movements):,})")
            batch_insert(cursor, 'stock_movements', movements)
            conn.commit()
        
        cursor.execute("SET FOREIGN_KEY_CHECKS = 1")
        cursor.execute("SET UNIQUE_CHECKS = 1")
        
        print("\n✅ Datos insertados correctamente!")
        print(f"   - {len(vocabularies)} vocabularios")
        print(f"   - {len(terms)} términos")
        print(f"   - {len(locations)} ubicaciones")
        print(f"   - {len(items):,} items")
        print(f"   - {len(identifiers):,} identificadores")
        print(f"   - {len(item_terms):,} relaciones item-term")
        print(f"   - {len(stock_items):,} stock items")
        print(f"   - {len(movements):,} movimientos")
        
    except Exception as e:
        conn.rollback()
        print(f"\n❌ Error: {e}")
        raise
    finally:
        cursor.close()
        conn.close()


def generate_sql_file(faker, output_file, items_count=1000, locations_count=50, stock_count=3000, movements_count=5000):
    """Genera archivo SQL completo"""
    
    print("Generando vocabularios...")
    vocabularies = faker.generate_vocabularies()
    
    print("Generando términos...")
    terms = faker.generate_terms(count_per_vocab=25)
    
    print(f"Generando {locations_count} ubicaciones...")
    locations = faker.generate_locations(count=locations_count, storage_units_per_location=5)
    
    print(f"Generando {items_count} items...")
    items = faker.generate_items(count=items_count)
    
    print("Generando identificadores...")
    identifiers = faker.generate_item_identifiers(items)
    
    print("Generando item-terms...")
    item_terms = faker.generate_item_terms(items)
    
    print(f"Generando {stock_count} stock items...")
    stock_items = faker.generate_stock_items(count=stock_count)
    
    movements = []
    if movements_count > 0:
        print(f"Generando {movements_count} movimientos...")
        movements = faker.generate_stock_movements(count=movements_count)
    
    print(f"Escribiendo a {output_file}...")
    
    with open(output_file, 'w', encoding='utf-8') as f:
        f.write("-- =====================================================\n")
        f.write("-- VESSEL CATALOG - FAKE DATA\n")
        f.write(f"-- Generated: {now()}\n")
        f.write("-- =====================================================\n\n")
        f.write("SET NAMES utf8mb4;\n")
        f.write("SET FOREIGN_KEY_CHECKS = 0;\n\n")
        
        f.write("-- VOCABULARIES\n")
        f.write(to_sql_insert('taxonomy_vocabularies', vocabularies) + '\n\n')
        
        f.write("-- TERMS\n")
        f.write(to_sql_insert('catalog_terms', terms) + '\n\n')
        
        f.write("-- LOCATIONS\n")
        # Primero las ubicaciones sin parent, luego las que tienen parent
        locations_root = [l for l in locations if l['parent_id'] is None]
        locations_children = [l for l in locations if l['parent_id'] is not None]
        f.write(to_sql_insert('locations_locations', locations_root) + '\n\n')
        if locations_children:
            f.write(to_sql_insert('locations_locations', locations_children) + '\n\n')
        
        f.write("-- ITEMS\n")
        f.write(to_sql_insert('catalog_items', items) + '\n\n')
        
        f.write("-- ITEM IDENTIFIERS\n")
        f.write(to_sql_insert('catalog_item_identifiers', identifiers) + '\n\n')
        
        f.write("-- ITEM TERMS (M:M)\n")
        f.write(to_sql_insert('catalog_item_terms', item_terms) + '\n\n')
        
        f.write("-- STOCK ITEMS\n")
        f.write(to_sql_insert('stock_items', stock_items) + '\n\n')
        
        if movements:
            f.write("-- STOCK MOVEMENTS\n")
            f.write(to_sql_insert('stock_movements', movements) + '\n\n')
        
        f.write("SET FOREIGN_KEY_CHECKS = 1;\n")
        f.write("-- END OF DATA\n")
    
    print(f"✅ Generado: {output_file}")
    print(f"   - {len(vocabularies)} vocabularios")
    print(f"   - {len(terms)} términos")
    print(f"   - {len(locations)} ubicaciones")
    print(f"   - {len(items)} items")
    print(f"   - {len(identifiers)} identificadores")
    print(f"   - {len(item_terms)} relaciones item-term")
    print(f"   - {len(stock_items)} stock items")
    print(f"   - {len(movements)} movimientos")


def generate_json_file(faker, output_file):
    """Genera archivo JSON con todos los datos"""
    
    data = {
        'vocabularies': faker.generate_vocabularies(),
        'terms': faker.generate_terms(count_per_vocab=25),
        'locations': faker.generate_locations(count=50),
        'items': faker.generate_items(count=1000),
    }
    
    data['item_identifiers'] = faker.generate_item_identifiers(data['items'])
    data['item_terms'] = faker.generate_item_terms(data['items'])
    data['stock_items'] = faker.generate_stock_items(count=3000)
    data['stock_movements'] = faker.generate_stock_movements(count=5000)
    
    with open(output_file, 'w', encoding='utf-8') as f:
        json.dump(data, f, indent=2, ensure_ascii=False)
    
    print(f"✅ Generado: {output_file}")


# =====================================================
# MAIN
# =====================================================

if __name__ == '__main__':
    parser = argparse.ArgumentParser(description='Vessel Catalog Data Faker')
    parser.add_argument('--items', type=int, default=1000, help='Número de items a generar')
    parser.add_argument('--locations', type=int, default=50, help='Número de ubicaciones')
    parser.add_argument('--stock', type=int, default=3000, help='Número de stock items')
    parser.add_argument('--movements', type=int, default=5000, help='Número de movimientos')
    parser.add_argument('--output', choices=['sql', 'json', 'direct'], default='direct', help='Modo de salida')
    parser.add_argument('--file', type=str, default=None, help='Nombre del archivo de salida')
    parser.add_argument('--workspace', type=str, default=None, help='Workspace ID')
    
    # DB connection args
    parser.add_argument('--host', type=str, default=os.getenv('DB_HOST', 'localhost'), help='MySQL host')
    parser.add_argument('--port', type=int, default=int(os.getenv('DB_PORT', '3307')), help='MySQL port')
    parser.add_argument('--user', type=str, default=os.getenv('DB_USER', 'root'), help='MySQL user')
    parser.add_argument('--password', type=str, default=os.getenv('DB_PASSWORD', ''), help='MySQL password')
    parser.add_argument('--database', type=str, default=os.getenv('DB_DATABASE', 'vessel_test'), help='MySQL database')
    parser.add_argument('--clean', action='store_true', help='Limpiar tablas antes de insertar')
    parser.add_argument('--only-movements', action='store_true', help='Solo insertar movements (usa locations existentes)')
    
    args = parser.parse_args()
    
    faker = VesselFaker(workspace_id=args.workspace)
    
    if args.output == 'direct':
        if not HAS_MYSQL:
            print("❌ Error: mysql-connector-python no está instalado")
            print("   Ejecuta: pip install mysql-connector-python")
            exit(1)
        
        insert_direct_to_db(
            faker,
            host=args.host,
            port=args.port,
            user=args.user,
            password=args.password,
            database=args.database,
            items_count=args.items,
            locations_count=args.locations,
            stock_count=args.stock,
            movements_count=args.movements,
            clean_tables=args.clean,
            only_movements=getattr(args, 'only_movements', False)
        )
    elif args.output == 'sql':
        output_file = args.file or 'vessel_fake_data.sql'
        generate_sql_file(
            faker, 
            output_file,
            items_count=args.items,
            locations_count=args.locations,
            stock_count=args.stock,
            movements_count=args.movements
        )
    else:
        output_file = args.file or 'vessel_fake_data.json'
        generate_json_file(faker, output_file)
