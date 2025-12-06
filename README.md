Vessel - Backend de inventario modular (BaaS)

Backend empresarial con **arquitectura hexagonal** y **microservicios**. Todo el runtime es **Laravel**; el frontend vive en otro repositorio y no es necesario para operar el backend.

## Requisitos (backend)
- PHP 8.2+, Composer
- Opcional: Docker Compose (un único contenedor Laravel; usa tu propia base de datos)

## Inicio rápido (backend dev)
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan serve
```

Con Docker (solo el backend Laravel; trae tu DB):
```bash
APP_PORT=8000 docker compose up -d catalog
```
Accesos:
- API: http://localhost:8000 (o el puerto que definas en `APP_PORT`)
- Admin panel (solo dev): http://localhost:8000/admin
- Healthchecks: `/health` y `/health/db`

Para levantar más de una instancia (ej. otro módulo), duplica el servicio `catalog` en `docker-compose.yml`, cambia `context`, `container_name` y `ports`, y levanta solo ese servicio:
```bash
# ejemplo para un módulo Inventory usando el mismo Dockerfile.dev
docker compose up -d inventory
```

## Módulos como plugins
- Los módulos viven en `app/<Module>` y se listan en `config/modules.php` con su `provider` y `migrations_path`.
- El panel `/admin` muestra módulos instalados, si el provider está cargado y cuántas migraciones están pendientes.
- WebSockets por módulo: activar `*_WS_ENABLED` en `.env` y usar `BROADCAST_CONNECTION=redis` (Redis ya en docker-compose).

### Instalar/activar un módulo
1) Asegúrate de que el código del módulo esté presente (por ejemplo, `app/Stock`).
2) Confirma que su `provider` y `migrations_path` estén en `config/modules.php` (ya precargados para stock, locations, taxonomy, uom, pricing, catalog, auth, portal).
3) Corre las migraciones del módulo si hay pendientes (visible en el panel admin) o manualmente con `php artisan migrate --path=app/<Module>/Infrastructure/Out/Database/Migrations`.
4) Reinicia la app si agregas nuevos providers.

### Añadir un módulo nuevo (plugin)
1) Coloca el código en `app/NuevoModulo` con su `Infrastructure/<...>/ServiceProvider` y migraciones.
2) Agrega su entrada a `config/modules.php` con `provider` y `migrations_path`.
3) Registra el provider en `bootstrap/providers.php` si deseas carga estática (o usa discovery en paquetes Composer si lo publicas).
4) Ejecuta migraciones y verifica en `/admin` que aparezca instalado.

## Actualización
- Comando dev-only `php artisan app:update` (habilitar `APP_ALLOW_UPDATE=true`) hace git pull + composer install + migrate. También disponible en el panel `/admin` → Actions → Auto Update.

## Distribución
- Este repo es la fuente; para publicar un “starter” o recibir PRs públicos, usa el flujo descrito en `docs/Distribution.md` (export con `scripts/export_distribution.sh`).

