---
sidebar_position: 1
---

---
sidebar_position: 1
slug: /
---

# Vessel API Documentation

Bienvenido a la documentación de **Vessel**, una API multi-módulo construida con Laravel 12 y arquitectura hexagonal.

## 🏗️ Arquitectura

Vessel sigue principios de **Clean Architecture** y **Hexagonal Architecture**:

```
modules/
├── catalog/           # Catálogo de productos
│   ├── Taxonomy/     # Sistema de categorización
│   ├── Product/      # Gestión de productos
│   └── Inventory/    # Control de inventario
└── ...
```

Cada módulo está estructurado en:

- **Domain**: Lógica de negocio pura (entidades, use cases, interfaces)
- **Infrastructure**: Adaptadores (controllers, repositories, service providers)

## 📚 Módulos Disponibles

### [Taxonomy](/modules/taxonomy)
Sistema de categorización mediante vocabularios y términos. Permite crear taxonomías jerárquicas para clasificar productos y otros elementos.

**Endpoints principales:**
- Vocabularies: CRUD completo
- Terms: CRUD + filtrado por vocabulario
- Relations: Gestión de relaciones entre términos

### Portal (Package)
Sistema de relaciones cross-service. Permite vincular entidades entre diferentes microservicios de forma agnóstica al framework.

**Características:**
- Framework-agnostic core
- Adaptadores para Laravel/Illuminate
- Publicado en Packagist: `thaumware/portal`

## 🚀 Inicio Rápido

### 1. Configurar API URL

Por defecto, los playgrounds apuntan a `http://localhost:8000/api`. Para cambiar:

```javascript
localStorage.setItem('API_URL', 'https://tu-api.com/api');
```

### 2. Levantar el backend

```bash
cd core
php artisan serve
```

### 3. Probar endpoints

Navega a cualquier módulo y usa los **API Playgrounds interactivos** para ejecutar requests directamente desde la documentación.

## 🎯 Principios de Diseño

1. **Domain-Driven Design**: Lógica de negocio separada de infraestructura
2. **Use Cases únicos**: Una responsabilidad por clase
3. **Dependency Injection**: Laravel resuelve automáticamente
4. **Controllers delgados**: Solo validación + delegación
5. **Testeable**: Mockear interfaces, no implementaciones

## 📖 Navegación

Usa la barra lateral para explorar:
- **Modules**: Documentación de cada módulo con API playgrounds
- **Packages**: Paquetes reutilizables (Portal, Core, etc.)
- **Architecture**: Guías de diseño y patrones

---

**API Base URL**: `http://localhost:8000/api`  
**Versión**: v1
