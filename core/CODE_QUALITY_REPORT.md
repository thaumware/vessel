# Vessel Code Quality Report

**Fecha**: 8 de diciembre de 2025

## 📊 Resumen Ejecutivo

### Herramientas Instaladas

1. **PHPStan 2.1.33** - Análisis estático de tipos y código
2. **PHPStan Strict Rules 2.0.7** - Reglas estrictas adicionales
3. **Custom Consistency Scanner** - Scanner personalizado para Vessel

### Estadísticas Generales

| Métrica | Valor |
|---------|-------|
| **Total de Errores (PHPStan)** | 2,317 |
| **Total de Tests** | 562 ✅ |
| **Assertions** | 1,712 ✅ |
| **Cobertura Tests** | Warnings: 2, Skipped: 9 |

## 🔍 Análisis de Errores por Categoría

### PHPStan (Nivel 6)

#### 1. **Dynamic Static Method Calls** - 1,553 errores (67%)
```php
// ❌ Problema
$this->assertEquals($expected, $actual);

// ✅ Solución
self::assertEquals($expected, $actual);
```
**Contexto**: PHPUnit assertions siendo llamadas dinámicamente en tests.  
**Prioridad**: BAJA (solo afecta tests, no código de producción)

#### 2. **Missing Type Hints** - 397 errores (17%)
```php
// ❌ Problema
function getData(): array { }
public $data;

// ✅ Solución
/** @return array<string, mixed> */
function getData(): array { }
/** @var array<string, mixed> */
public array $data;
```
**Prioridad**: MEDIA (mejora mantenibilidad y previene bugs)

#### 3. **empty() Usage** - 29 errores (1.3%)
```php
// ❌ Problema
if (empty($data)) { }

// ✅ Solución
if ($data === null || $data === '' || count($data) === 0) { }
```
**Prioridad**: BAJA (estilo, no afecta funcionalidad)

#### 4. **Non-boolean Conditions** - 23 errores (1%)
```php
// ❌ Problema
if ($config['enabled']) { }

// ✅ Solución
if (($config['enabled'] ?? false) === true) { }
```
**Prioridad**: MEDIA (puede prevenir bugs sutiles)

### Custom Consistency Scanner

#### 1. **snake_case Methods** - 583 ocurrencias (28%)
```php
// ❌ Problema en Domain/Application
public function test_user_can_login() { }

// ✅ Solución
public function testUserCanLogin() { }

// ✅ OK en Tests (convención PHPUnit)
public function test_user_can_login() { }
```
**Contexto**: Tests usan `test_*` por convención, pero código de producción debe usar camelCase.  
**Prioridad**: ALTA (inconsistencia de nomenclatura)

#### 2. **Mixed Casing in Same Line** - 1,465 ocurrencias (70%)
```php
// ❌ Problema
$catalog_item = $this->itemRepository->findById($item_id);

// ✅ Solución (dominio)
$catalogItem = $this->itemRepository->findById($itemId);

// ✅ Solución (infraestructura/BD)
$catalog_item = DB::table('catalog_items')->where('item_id', $id)->first();
```
**Prioridad**: ALTA (inconsistencia crítica)

#### 3. **Short Ternary (?:)** - 15 ocurrencias (0.7%)
```php
// ❌ Problema
$value = $config['key'] ?: 'default';

// ✅ Solución
$value = $config['key'] ?? 'default';
```
**Prioridad**: BAJA (estilo, pero ?? es más claro)

#### 4. **empty() Usage** - 30 ocurrencias (1.4%)
**Prioridad**: BAJA (duplicado con PHPStan)

## 🎯 Plan de Acción Recomendado

### Fase 1: Crítico (Hacer AHORA)
- [ ] **Definir convención de naming clara**:
  - Domain/Application: `camelCase` para métodos/propiedades
  - Infrastructure (DB): `snake_case` para columnas/keys OK
  - Tests: `test_snake_case` OK (convención PHPUnit)

- [ ] **Crear archivo `.editorconfig`**:
```ini
[*.php]
indent_style = space
indent_size = 4

[app/{Domain,Application}/**/*.php]
# Enforce camelCase in domain layer
```

### Fase 2: Alta Prioridad (Esta semana)
- [ ] Migrar métodos de dominio de `snake_case` → `camelCase`
- [ ] Agregar type hints a métodos públicos críticos
- [ ] Actualizar PHPStan config para ignorar tests:
```neon
parameters:
    excludePaths:
        - app/*/Tests/**
```

### Fase 3: Media Prioridad (Próximas 2 semanas)
- [ ] Reemplazar `empty()` con comparaciones estrictas
- [ ] Reemplazar `?:` con `??`
- [ ] Agregar PHPDoc con generics: `@return array<string, Item>`

### Fase 4: Baja Prioridad (Backlog)
- [ ] Refactorizar tests para usar `self::assert*()` en vez de `$this->assert*()`
- [ ] Nivel PHPStan 7-8 (muy estricto, opcional)

## 🛠️ Comandos Útiles

### Análisis Completo
```bash
# PHPStan (análisis estático)
vendor/bin/phpstan analyze --memory-limit=1G

# Scanner personalizado
php scan-consistency.php

# Tests
vendor/bin/phpunit --testdox
```

### Análisis por Módulo
```bash
# Solo Stock
vendor/bin/phpstan analyze app/Stock --level 6

# Solo Catalog
vendor/bin/phpstan analyze app/Catalog --level 6
```

### CI/CD Integration
```yaml
# .github/workflows/code-quality.yml
- name: PHPStan
  run: vendor/bin/phpstan analyze --error-format=github --no-progress

- name: Consistency Check
  run: php scan-consistency.php
```

## 📈 Métricas de Progreso

| Semana | snake_case | Mixed Casing | Missing Types | PHPStan Errors |
|--------|------------|--------------|---------------|----------------|
| Actual | 583 | 1,465 | 397 | 2,317 |
| Meta S1 | < 300 | < 800 | 300 | < 2,000 |
| Meta S2 | < 100 | < 200 | 200 | < 1,500 |
| Meta Final | 0 (solo tests) | < 50 | 100 | < 1,000 |

## 🔗 Referencias

- [PHPStan Documentation](https://phpstan.org/user-guide/getting-started)
- [PSR-12 Coding Style](https://www.php-fig.org/psr/psr-12/)
- [Clean Architecture in PHP](https://herbertograca.com/2017/11/16/explicit-architecture-01-ddd-hexagonal-onion-clean-cqrs-how-i-put-it-all-together/)

---

**Generado por**: Vessel Code Quality Scanner  
**Contacto**: Run `php scan-consistency.php --help` for more options
