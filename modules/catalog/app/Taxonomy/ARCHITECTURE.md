# Arquitectura del Módulo Taxonomy

## Visión General

El módulo **Taxonomy** implementa una arquitectura hexagonal para la gestión de taxonomías y vocabularios en el sistema. Soporta múltiples adaptadores de persistencia que se seleccionan dinámicamente mediante headers HTTP.

## Arquitectura Hexagonal

```
           ┌─────────────────────────────────────┐
           │           APPLICATION LAYER          │
           │                                     │
           │  ┌─────────────────────────────────┐ │
           │  │        USE CASES                │ │
           │  │  - CreateVocabulary             │ │
           │  │  - ListVocabularies             │ │
           │  │  - GetVocabulary                │ │
           │  │  - UpdateVocabulary             │ │
           │  │  - DeleteVocabulary             │ │
           │  │  - CreateTerm                   │ │
           │  │  - ListTerms                    │ │
           │  │  - GetTerm                      │ │
           │  │  - UpdateTerm                   │ │
           │  │  - DeleteTerm                   │ │
           │  │  - AddTermRelation              │ │
           │  │  - RemoveTermRelation           │ │
           │  │  - GetTermTree                  │ │
           │  │  - GetTermBreadcrumb            │ │
           │  └─────────────────────────────────┘ │
           └─────────────────────────────────────┘
                           │
                           │ (interfaces)
                           │
           ┌─────────────────────────────────────┐
           │          DOMAIN LAYER               │
           │                                     │
           │  ┌─────────────────────────────────┐ │
           │  │        ENTITIES                 │ │
           │  │  - Vocabulary                   │ │
           │  │  - Term                         │ │
           │  │  - TermRelation                 │ │
           │  └─────────────────────────────────┘ │
           │                                     │
           │  ┌─────────────────────────────────┐ │
           │  │      INTERFACES                 │ │
           │  │  - VocabularyRepositoryInterface│ │
           │  │  - TermRepositoryInterface      │ │
           │  │  - TermRelationRepositoryInterface│ │
           │  └─────────────────────────────────┘ │
           └─────────────────────────────────────┘
                           │
                           │ (implementations)
                           │
           ┌─────────────────────────────────────┐
           │       INFRASTRUCTURE LAYER          │
           │                                     │
           │  ┌─────────────────────────────────┐ │
           │  │     IN (HTTP)                   │ │
           │  │  - TaxonomyController           │ │
           │  │  - TaxonomyRoutes               │ │
           │  │  - AdapterMiddleware (compartido)│ │
           │  └─────────────────────────────────┘ │
           │                                     │
           │  ┌─────────────────────────────────┐ │
           │  │     OUT (PERSISTENCE)           │ │
           │  │  - Eloquent Repositories        │ │
           │  │  - InMemory Repositories (TODO) │ │
           │  └─────────────────────────────────┘ │
           └─────────────────────────────────────┘
```

## Estructura de Archivos

```
app/Taxonomy/
├── Domain/                             # 📦 Capa de Dominio (pura)
│   ├── Entities/                       # Entidades del negocio
│   │   ├── Vocabulary.php              # Vocabulario (categoría)
│   │   ├── Term.php                    # Término (elemento)
│   │   └── TermRelation.php            # Relación entre términos
│   ├── DTOs/                           # Objetos de Transferencia de Datos
│   │   └── TermTreeNode.php            # Nodo del árbol de términos
│   └── Interfaces/                     # Puertos/Contratos
│       ├── VocabularyRepositoryInterface.php
│       ├── TermRepositoryInterface.php
│       └── TermRelationRepositoryInterface.php
└── Infrastructure/                     # 🔧 Capa de Infraestructura
    ├── TaxonomyServiceProvider.php     # Proveedor de servicios
    ├── In/                             # 👥 Adaptadores de Entrada
    │   └── Http/
    │       ├── Controllers/
    │       │   └── TaxonomyController.php
    │       └── Routes/
    │           └── TaxonomyRoutes.php
    └── Out/                            # 💾 Adaptadores de Salida
        └── Models/
            └── Eloquent/
                ├── VocabularyModel.php
                ├── TermModel.php
                └── TermRelationModel.php
```

## Adaptadores de Persistencia

### 🔄 Adaptador Dinámico
- **Middleware**: `AdapterMiddleware` compartido (`app/Shared/Infrastructure/Middleware/AdapterMiddleware`)
- **Header**: `X-TAXONOMY-ADAPTER` (local = In-Memory, otros = Eloquent)
- **SQL** (por defecto): Usa Eloquent ORM con MySQL/PostgreSQL
- **Local** (In-Memory): Pendiente de implementar

### 📊 Esquema de Base de Datos

```sql
-- Vocabularios
CREATE TABLE taxonomy_vocabularies (
    id UUID PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    workspace_id UUID,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Términos
CREATE TABLE taxonomy_terms (
    id UUID PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    description TEXT,
    vocabulary_id UUID NOT NULL,
    parent_id UUID NULL,
    workspace_id UUID,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Relaciones entre términos
CREATE TABLE taxonomy_term_relations (
    id UUID PRIMARY KEY,
    term_id UUID NOT NULL,
    related_term_id UUID NOT NULL,
    relation_type VARCHAR(50) NOT NULL,
    workspace_id UUID,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL,
    UNIQUE(term_id, related_term_id, relation_type)
);
```

## API Endpoints

### Vocabularios
- `POST /api/v1/taxonomy/vocabularies/create` - Crear vocabulario
- `GET /api/v1/taxonomy/vocabularies/read` - Listar vocabularios
- `GET /api/v1/taxonomy/vocabularies/show/{id}` - Obtener vocabulario
- `PUT /api/v1/taxonomy/vocabularies/update/{id}` - Actualizar vocabulario
- `DELETE /api/v1/taxonomy/vocabularies/delete/{id}` - Eliminar vocabulario

### Términos
- `POST /api/v1/taxonomy/terms/create` - Crear término
- `GET /api/v1/taxonomy/terms/read` - Listar términos
- `GET /api/v1/taxonomy/terms/show/{id}` - Obtener término
- `PUT /api/v1/taxonomy/terms/update/{id}` - Actualizar término
- `DELETE /api/v1/taxonomy/terms/delete/{id}` - Eliminar término
- `GET /api/v1/taxonomy/terms/tree` - Obtener árbol de términos
- `GET /api/v1/taxonomy/terms/breadcrumb/{id}` - Obtener breadcrumb de navegación

### Relaciones
- `POST /api/v1/taxonomy/terms/relations/add` - Agregar relación
- `POST /api/v1/taxonomy/terms/relations/remove` - Remover relación

## Headers para Adaptadores

```bash
# Usar SQL (por defecto)
GET /api/v1/taxonomy/vocabularies/read

# Usar In-Memory (cuando esté implementado)
GET /api/v1/taxonomy/vocabularies/read
Header: X-TAXONOMY-ADAPTER: local
```

## Próximas Implementaciones

- ✅ Arquitectura hexagonal básica
- ✅ Repositorios Eloquent
- ✅ Middleware de adapter compartido
- 🔄 Repositorios In-Memory
- 🔄 Tests unitarios
- 🔄 Documentación Docusaurus