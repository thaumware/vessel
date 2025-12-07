#!/usr/bin/env bash
set -euo pipefail
cd "$(dirname "$0")/.."

COMPOSE_FILE=docker-compose.prod.yml
APP_SERVICE=core

# Ensure .env exists
if [ ! -f .env ]; then
  echo ".env no encontrado; copiando desde .env.example"
  cp .env.example .env
fi

# Ensure sqlite file if using sqlite
DB_PATH=$(grep -E '^DB_DATABASE=' .env | head -n1 | cut -d= -f2- || true)
if [ -n "$DB_PATH" ] && [[ "$DB_PATH" == *.sqlite ]]; then
  mkdir -p "$(dirname "$DB_PATH")"
  if [ ! -f "$DB_PATH" ]; then
    echo "Creando $DB_PATH"
    touch "$DB_PATH"
  fi
fi

# Build and start containers
DOCKER_BUILDKIT=1 docker compose -f "$COMPOSE_FILE" up -d --build

# Generate app key
if ! docker compose -f "$COMPOSE_FILE" exec -T "$APP_SERVICE" php artisan key:generate --force; then
  echo "Fallo generando APP_KEY" >&2
  exit 1
fi

# Run migrations
if ! docker compose -f "$COMPOSE_FILE" exec -T "$APP_SERVICE" php artisan migrate --force; then
  echo "Fallo ejecutando migraciones" >&2
  exit 1
fi

echo "Instalación completada. Visita http://localhost:8000"
