#!/bin/bash

# Vessel Setup Verification Script
# Este script verifica que todo esté configurado correctamente

set -e

BOLD='\033[1m'
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Contadores
PASSED=0
FAILED=0
WARNINGS=0

# Funciones de colores
pass() {
    echo -e "${GREEN}✓ $1${NC}"
    ((PASSED++))
}

fail() {
    echo -e "${RED}✗ $1${NC}"
    ((FAILED++))
}

warn() {
    echo -e "${YELLOW}⚠ $1${NC}"
    ((WARNINGS++))
}

info() {
    echo -e "${BOLD}ℹ $1${NC}"
}

echo -e "${BOLD}🚀 Vessel Setup Verification Script${NC}\n"

# ============================================
# SECCIÓN 1: Verificación de Herramientas
# ============================================
echo -e "${BOLD}1. Verificando herramientas disponibles...${NC}"

if command -v docker &> /dev/null; then
    pass "Docker instalado: $(docker --version)"
else
    fail "Docker no encontrado"
fi

if command -v docker-compose &> /dev/null; then
    pass "Docker Compose instalado: $(docker-compose --version)"
else
    fail "Docker Compose no encontrado"
fi

# ============================================
# SECCIÓN 2: Verificación de Puertos
# ============================================
echo -e "\n${BOLD}2. Verificando disponibilidad de puertos...${NC}"

if [[ "$OSTYPE" == "darwin"* ]]; then
    # Mac
    if ! lsof -i :80 &> /dev/null; then
        pass "Puerto 80 disponible"
    else
        fail "Puerto 80 en uso: $(lsof -i :80 | grep -v COMMAND)"
    fi
    
    if ! lsof -i :8080 &> /dev/null; then
        pass "Puerto 8080 disponible"
    else
        fail "Puerto 8080 en uso: $(lsof -i :8080 | grep -v COMMAND)"
    fi
elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
    # Linux
    if ! netstat -tuln 2>/dev/null | grep :80 &> /dev/null; then
        pass "Puerto 80 disponible"
    else
        fail "Puerto 80 en uso"
    fi
    
    if ! netstat -tuln 2>/dev/null | grep :8080 &> /dev/null; then
        pass "Puerto 8080 disponible"
    else
        fail "Puerto 8080 en uso"
    fi
elif [[ "$OSTYPE" == "msys" ]] || [[ "$OSTYPE" == "win32" ]]; then
    # Windows (Git Bash)
    info "Windows detectado: usar 'netstat -ano | findstr :80' para verificar puertos manualmente"
fi

# ============================================
# SECCIÓN 3: Verificación de Archivos
# ============================================
echo -e "\n${BOLD}3. Verificando archivos necesarios...${NC}"

if [ -f ".env" ]; then
    pass "Archivo .env existe"
    
    # Verificar variables importantes
    if grep -q "DB_HOST=" .env; then
        pass "DB_HOST configurado en .env"
    else
        fail "DB_HOST no configurado en .env"
    fi
    
    if grep -q "DB_CATALOG_DATABASE=" .env; then
        pass "DB_CATALOG_DATABASE configurado"
    else
        fail "DB_CATALOG_DATABASE no configurado"
    fi
else
    fail "Archivo .env no encontrado (copia desde .env.example)"
fi

if [ -f "docker-compose.yml" ]; then
    pass "docker-compose.yml existe"
else
    fail "docker-compose.yml no encontrado"
fi

if [ -d "core" ]; then
    pass "Core existe"
else
    fail "Core no encontrado"
fi

if [ -d "modules/inventory" ]; then
    pass "Módulo inventory existe"
else
    fail "Módulo inventory no encontrado"
fi

if [ -d "modules/vessel" ]; then
    pass "Módulo vessel existe"
else
    fail "Módulo vessel no encontrado"
fi

# ============================================
# SECCIÓN 4: Verificación de /etc/hosts
# ============================================
echo -e "\n${BOLD}4. Verificando configuración de dominios locales (/etc/hosts)...${NC}"

if [[ "$OSTYPE" == "win32" ]]; then
    HOSTS_FILE="C:\Windows\System32\drivers\etc\hosts"
    info "Windows: Verificar manualmente $HOSTS_FILE"
else
    HOSTS_FILE="/etc/hosts"
fi

for domain in "catalog.vessel.dev" "inventory.vessel.dev" "vessel.vessel.dev" "traefik.vessel.dev"; do
    if grep -q "$domain" "$HOSTS_FILE"; then
        pass "$domain configurado en /etc/hosts"
    else
        fail "$domain NO configurado en /etc/hosts"
    fi
done

# ============================================
# SECCIÓN 5: Verificación de Docker (si está activo)
# ============================================
echo -e "\n${BOLD}5. Verificando estado de Docker...${NC}"

if docker ps &> /dev/null; then
    pass "Docker daemon activo"
    
    # Contar contenedores de Vessel
    count=$(docker-compose ps --services 2>/dev/null | wc -l)
    if [ "$count" -gt 0 ]; then
        warn "Servicios Docker detectados: $count (ejecutar 'docker-compose down' si es previo a startup)"
    fi
else
    warn "Docker daemon no activo (necesario para levantar servicios)"
fi

# ============================================
# SECCIÓN 6: Verificación de Red Docker
# ============================================
echo -e "\n${BOLD}6. Verificando red Docker...${NC}"

if docker network ls | grep -q "vessel_network"; then
    pass "Red vessel_network existe"
else
    warn "Red vessel_network no existe (se creará al ejecutar docker-compose up)"
fi

# ============================================
# SECCIÓN 7: Verificación de MySQL Conectividad
# ============================================
echo -e "\n${BOLD}7. Verificando conectividad a MySQL...${NC}"

if grep -q "DB_HOST=" .env; then
    DB_HOST=$(grep "DB_HOST=" .env | cut -d '=' -f2 | tr -d ' ')
    DB_USER=$(grep "DB_USERNAME=" .env | cut -d '=' -f2 | tr -d ' ')
    DB_PASS=$(grep "DB_PASSWORD=" .env | cut -d '=' -f2 | tr -d ' ')
    
    if command -v mysql &> /dev/null; then
        if mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "SELECT 1" &> /dev/null; then
            pass "MySQL accesible en $DB_HOST"
        else
            fail "MySQL NO accesible en $DB_HOST (verifica credenciales)"
        fi
    else
        warn "MySQL CLI no instalado (sáltate esta verificación)"
    fi
else
    warn "No se pudo leer DB_HOST de .env"
fi

# ============================================
# SECCIÓN 8: Verificación de Dockerfile.dev
# ============================================
echo -e "\n${BOLD}8. Verificando Dockerfiles...${NC}"

for module in catalog inventory vessel; do
    if [ -f "modules/$module/Dockerfile.dev" ]; then
        pass "modules/$module/Dockerfile.dev existe"
    else
        fail "modules/$module/Dockerfile.dev NO encontrado"
    fi
done

# ============================================
# SECCIÓN 9: Verificación de composer.json por módulo
# ============================================
echo -e "\n${BOLD}9. Verificando estructura Laravel...${NC}"

for module in catalog inventory vessel; do
    if [ -f "modules/$module/composer.json" ]; then
        pass "modules/$module/composer.json existe"
    else
        fail "modules/$module/composer.json NO encontrado"
    fi
done

# ============================================
# RESUMEN FINAL
# ============================================
echo -e "\n${BOLD}════════════════════════════════════════${NC}"
echo -e "${BOLD}Resumen de Verificación${NC}"
echo -e "${BOLD}════════════════════════════════════════${NC}"

echo -e "${GREEN}✓ Pasadas: $PASSED${NC}"
if [ $FAILED -gt 0 ]; then
    echo -e "${RED}✗ Fallos: $FAILED${NC}"
else
    echo -e "${GREEN}✗ Fallos: 0${NC}"
fi
if [ $WARNINGS -gt 0 ]; then
    echo -e "${YELLOW}⚠ Advertencias: $WARNINGS${NC}"
fi

echo ""

# Recomendaciones finales
if [ $FAILED -eq 0 ] && [ $WARNINGS -le 2 ]; then
    echo -e "${GREEN}${BOLD}✓ ¡Listo para arrancar!${NC}"
    echo ""
    echo -e "Próximos pasos:"
    echo -e "  1. ${BOLD}docker-compose up -d${NC}"
    echo -e "  2. Esperar ~10 segundos"
    echo -e "  3. Visitar: ${BOLD}http://localhost:8080${NC} (Traefik Dashboard)"
    echo -e "  4. Probar APIs:"
    echo -e "     • curl http://catalog.vessel.dev/"
    echo -e "     • curl http://inventory.vessel.dev/"
    echo -e "     • curl http://vessel.vessel.dev/"
    echo ""
    echo -e "Ver guía completa: ${BOLD}docs/SETUP.md${NC}"
else
    echo -e "${RED}${BOLD}✗ Hay problemas que solucionar${NC}"
    echo ""
    echo -e "Acciones recomendadas:"
    echo -e "  1. Revisar los fallos marcados arriba"
    echo -e "  2. Consultar ${BOLD}docs/SETUP.md${NC} para configuración"
    echo -e "  3. Asegurar: archivo .env, puertos libres, /etc/hosts, MySQL accesible"
    echo -e "  4. Reintentar: ${BOLD}bash scripts/verify.sh${NC}"
fi

echo ""

exit $FAILED
