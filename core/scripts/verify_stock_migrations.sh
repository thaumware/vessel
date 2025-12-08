#!/bin/bash

# Script para verificar el estado de las migraciones de Stock
# y correr las que falten

cd "$(dirname "$0")/.."

echo "=== Verificando migraciones de Stock ==="
echo ""

# Verificar si Docker está corriendo
if ! docker compose ps mysql | grep -q "Up"; then
    echo "❌ MySQL no está corriendo. Iniciando contenedor..."
    docker compose up -d mysql
    echo "⏳ Esperando a que MySQL esté listo..."
    sleep 10
fi

echo "✅ MySQL está corriendo"
echo ""

# Verificar estado de migraciones
echo "📋 Estado de migraciones:"
php artisan migrate:status | grep -i stock || echo "⚠️  No se encontraron migraciones de Stock en el registro"
echo ""

# Verificar schema de stock_movements
echo "🔍 Verificando tabla stock_movements:"
docker compose exec -T mysql mysql -u root -proot vessel -e "SHOW COLUMNS FROM stock_movements" 2>/dev/null

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Tabla stock_movements existe"
    
    # Verificar columna movement_type específicamente
    if docker compose exec -T mysql mysql -u root -proot vessel -e "SHOW COLUMNS FROM stock_movements WHERE Field='movement_type'" 2>/dev/null | grep -q "movement_type"; then
        echo "✅ Columna movement_type existe"
    else
        echo "❌ Columna movement_type NO existe - MIGRACIÓN FALTANTE"
        echo ""
        echo "🔧 Corriendo migraciones pendientes..."
        php artisan migrate --path=app/Stock/Infrastructure/Out/Database/Migrations --force
    fi
else
    echo "❌ Tabla stock_movements NO existe - MIGRACIONES FALTANTES"
    echo ""
    echo "🔧 Corriendo todas las migraciones de Stock..."
    php artisan migrate --path=app/Stock/Infrastructure/Out/Database/Migrations --force
fi

echo ""
echo "=== Verificación completa ==="
