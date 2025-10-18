# 🚢 Vessel - Sistema de Inventario Modular

Sistema de inventario empresarial con **arquitectura hexagonal** y **microservicios**, construido con **Bazel**, **Laravel** (backend) y **React + TypeScript** (frontend).

## 🏗️ Arquitectura

### Características Principales

- ✅ **Arquitectura Hexagonal** (Puertos y Adaptadores)
- ✅ **Microservicios** independientes y escalables
- ✅ **Monorepo con Bazel** para builds incrementales ultra-rápidos
- ✅ **Modo Dual**: Standalone (autónomo) o Integrated (con servicios externos)
- ✅ **Domain-Driven Design** (DDD)
- ✅ **Event-Driven Architecture**

### Stack Tecnológico

**Backend:**
- Laravel 11 (PHP 8.2+)
- PostgreSQL / SQLite
- Redis, RabbitMQ

**Frontend:**
- React 18 + TypeScript 5
- Vite, TanStack Query

**Infraestructura:**
- Bazel (Build system)
- Docker & Kubernetes
- gRPC / REST

## 📁 Nueva Estructura del Proyecto

```
vessel/
├── modules/              # Microservicios (backend Laravel)
│   ├── inventory-core/   # Core del inventario ⭐
│   ├── auth-service/     # Autenticación
│   ├── iot-service/      # IoT (NFC, RFID)
│   └── notification-service/
├── apps/                 # Aplicaciones frontend
│   ├── admin-web/        # Dashboard admin (React) ⭐
│   └── warehouse-pwa/    # App almacén (PWA)
├── packages/             # Paquetes compartidos
│   ├── domain-kernel/    # DDD building blocks
│   ├── integration-sdk/  # SDK para integración
│   ├── ui-components/    # Componentes UI
│   └── api-contracts/    # Contratos API
├── infrastructure/       # Infraestructura
│   ├── docker/          # Docker configs ⭐
│   ├── kubernetes/      # K8s manifests
│   └── gateway/         # API Gateway
├── deployment-profiles/ # Perfiles de despliegue
└── backend/            # ⚠️ Legacy - migrar a modules/
└── frontend/           # ⚠️ Legacy - migrar a apps/
```

> **⚠️ NOTA**: Los directorios `backend/` y `frontend/` existentes serán migrados gradualmente a la nueva estructura modular.

## 🚀 Inicio Rápido

### Prerrequisitos

- **Node.js** 20+
- **PHP** 8.2+
- **Composer**
- **Docker** & Docker Compose (recomendado)
- **Bazel** 7.0.0 (opcional, para builds optimizados)

### Instalación

```bash
# Instalar dependencias raíz
npm install

# Instalar dependencias módulos legacy (mientras se migra)
cd backend && composer install
cd ../frontend && npm install
```

### Desarrollo Local

**Opción 1: Docker Compose (Recomendado) 🐳**
```bash
# Levantar todos los servicios
npm run docker:up

# Acceder a:
# - Frontend Admin (nuevo): http://localhost:3000
# - Frontend (legacy): http://localhost:5173
# - API Inventory: http://localhost:8000
# - RabbitMQ UI: http://localhost:15672
```

**Opción 2: Modo Legacy (Backend + Frontend actuales)**
```powershell
# Terminal 1 - Backend Laravel (legacy)
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --force
php artisan serve --host=127.0.0.1 --port=8000

# Terminal 2 - Frontend React (legacy)
cd frontend
npm install
npm run dev
```

**Opción 3: Nueva Arquitectura**
```bash
# Terminal 1 - Inventory Core Module (nuevo)
cd modules/inventory-core
composer install
php artisan serve --port=8000

# Terminal 2 - Admin Web (nuevo)
cd apps/admin-web
npm install
npm run dev
```

### Build con Bazel

```bash
# Build completo
bazel build //...

# Build nuevo frontend admin
bazel build //apps/admin-web:admin-web

# Tests
bazel test //...
```

## 🔧 Configuración

### Modo Standalone (nuevo)
```env
VESSEL_MODE=standalone
DB_CONNECTION=sqlite
AUTH_TYPE=internal
IOT_MOCK_DEVICES=true
```

### Modo Integrated (nuevo)
```env
VESSEL_MODE=integrated
DB_CONNECTION=pgsql
AUTH_SERVICE_URL=https://auth.mycompany.com
IOT_SERVICE_URL=https://iot.mycompany.com
```

## 🧪 Testing

```bash
# Tests unitarios
npm run test:unit

# Tests de integración
npm run test:integration

# Coverage
npm run test:coverage
```

## 📖 Documentación

- [Guía de Arquitectura](./docs/architecture/README.md)
- [API Reference](./docs/api/README.md)
- [Deployment Guide](./docs/deployment/README.md)
- [Integration Guide](./examples/README.md)

## 🐳 Docker

```bash
# Desarrollo (todos los servicios)
docker-compose -f infrastructure/docker/docker-compose.dev.yml up

# Solo backend + frontend legacy
cd backend && php artisan serve
cd frontend && npm run dev
```

## 🔄 Plan de Migración

1. ✅ **Fase 1**: Estructura de carpetas creada
2. ⏳ **Fase 2**: Migrar `backend/` → `modules/inventory-core/`
3. ⏳ **Fase 3**: Migrar `frontend/` → `apps/admin-web/`
4. ⏳ **Fase 4**: Implementar servicios adicionales (auth, iot)
5. ⏳ **Fase 5**: Full Bazel integration

## Troubleshooting

- Si `npm install` falla con peer dependencies:
  ```bash
  npm install --legacy-peer-deps
  ```
- Si Vite falla, usa el config correcto: `frontend/vite.config.ts`
- Los TypeScript errors en archivos legacy son temporales

## 🔗 Links Útiles

- [Bazel Documentation](https://bazel.build/)
- [Laravel Documentation](https://laravel.com/docs)
- [React Documentation](https://react.dev/)
- [Hexagonal Architecture](https://alistair.cockburn.us/hexagonal-architecture/)

---

**🚢 Desarrollado por el equipo Vessel**