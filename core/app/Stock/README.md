**Stock Module — Convenciones y Buenas Prácticas**

- Estructura
  - Domain/: Entidades e interfaces del dominio.
  - Application/: Casos de uso (UseCases) puros — no deben contener detalles de infraestructura.
  - Infrastructure/In/: Adaptadores entrantes (HTTP controllers, webhooks, CLI).
  - Infrastructure/Out/: Adaptadores salientes (Eloquent repos, queue consumers, integraciones externas).
  - Infrastructure/Out/Database/Migrations/: Migrations del módulo.

- Migraciones / Columnas
  - Todas las tablas usan `uuid` como PK (`id` UUID) para consistencia entre módulos.
  - Las tablas contienen `workspace_id` UUID nullable para soportar multi-tenant / espacios de trabajo.
  - Usamos campos de fecha explícitos: `created_at`, `updated_at`, `deleted_at` como `dateTime(...)->nullable()` (coincide con `Taxonomy`).
  - Preferir índices explícitos sobre columnas consultadas frecuentemente (`sku`, `location_id`, `code`).

- Eloquent Models
  - Los modelos extienden `App\Shared\Adapters\Eloquent\EloquentModel` que aplica:
    - `SoftDeletes`
    - `public $primaryKey = 'id'`, `public $keyType = 'string'`, `public $incrementing = false` y `public $timestamps = true`.
  - Definir `$fillable` para los campos que serán mass assigned y `$casts` para `created_at`, `updated_at`, `deleted_at`.

- Use Cases y Adapters
  - Los UseCases viven en `Application/UseCases` y encapsulan reglas de negocio.
  - Adaptadores entrantes (HTTP/webhook) deben validar y delegar a UseCases; para cargas/peticiones largas, encolar un Job que invoque al UseCase.
  - No exponer repositorios directamente a controladores; siempre inyectar UseCases.

- Webhooks / Sincronización y Colas
  - Recomendado procesar webhooks de forma asíncrona: controlador valida y `dispatch()` un Job (`Infrastructure/Jobs`) que llame al UseCase.
  - Driver de colas recomendado: `redis` (produktion) o `database` (dev/small). Configurar `QUEUE_CONNECTION` en `.env`.
  - Para idempotencia, incluir `movement_id` o `idempotency_key` en payload y persistir en tabla `stock_movements` antes de procesar.
  - Usar Outbox pattern si necesitas emitir eventos de integración atómicamente junto con cambios de BD.

- Auditoría
  - Crear `stock_movements` para auditoría y control de idempotencia; almacenar `movement_id`, `payload`, `status`, `attempts`, `processed_at`.

Movimientos (Kardex)
- Agregamos la tabla `stock_movements` para llevar un kardex de movimientos.
- Campos clave:
  - `movement_id`: id externo / idempotency key.
  - `sku`, `location_from`, `location_to`, `quantity`, `balance_after`.
  - `movement_type`: `in|out|transfer|adjustment`.
  - `reference`, `user_id`, `workspace_id`, `meta`.
- Recomendación: al procesar un movimiento guardar primero el registro en `stock_movements` (con `movement_id`) dentro de la misma transacción donde se actualiza el `stock_current` (outbox pattern opcional).

Polimorfismo de ubicaciones (UUID morph)
- Las ubicaciones (`location`) pueden provenir de módulos externos. Para soportarlo usamos columnas polymórficas con UUIDs:
  - `location_from_id` (UUID) y `location_from_type` (string)
  - `location_to_id` (UUID) y `location_to_type` (string)
- Al recibir hooks o eventos externos, envía el par `*_id` + `*_type` para que el módulo pueda identificar el origen exacto de la ubicación.

Ejemplo payload webhook (transferencia):

```
{
  "sku": "SKU123",
  "from_location_id": "a1b2c3d4-...",
  "from_location_type": "App\\Locations\\Infrastructure\\Out\\Models\\Eloquent\\LocationModel",
  "to_location_id": "d4c3b2a1-...",
  "to_location_type": "App\\Warehouse\\Infrastructure\\Out\\Models\\Eloquent\\WarehouseModel",
  "quantity": 10,
  "movement_id": "external-evt-123",
  "movement_type": "transfer",
  "reference": "ORDER-99",
  "meta": { "source": "erp" }
}
```

Idempotencia y auditoría
- Incluye `movement_id` para evitar duplicados. El repositorio `MovementRepositoryInterface` permite buscar por `movement_id`.

- Tests
  - Escribir tests unitarios para UseCases y tests de integración para los adaptadores.

- Migraciones existentes en producción
  - Si ya aplicaste migraciones antiguas, crear migrations de transición (no sobrescribir) que:
    - Añadan `id` UUID, `workspace_id`, `created_at`, `updated_at` si faltan,
    - Rellenen valores desde columnas existentes,
    - Añadan índices y constraints y finalmente eliminen estructuras antiguas si procede.

- Ejemplo de flujo para un movimiento (webhook)
  - Controller recibe `POST /api/v1/stock/webhooks/movement` -> valida y `dispatch(new ApplyMovementJob($payload))` -> Job ejecuta `ApplyMovement` dentro de transacción -> guarda registro en `stock_movements` -> si todo ok emite confirmación o evento.

Si quieres, aplico ahora:
- Generar migration `stock_movements` y el Job `ApplyMovementJob` + adaptar webhook para encolar.
- O generar migrations de transición para tablas ya existentes.

Indica lo que prefieres y lo implemento.