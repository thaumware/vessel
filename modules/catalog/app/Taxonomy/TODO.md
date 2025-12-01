# Taxonomy Module - TODO

## Estado: Funcional

### Dominio
- [x] Entidad `Category`
- [x] Entidad `Attribute`
- [x] Entidad `AttributeValue`
- [x] Soporte jerarquía categorías (parent_id)
- [x] Interface `CategoryRepositoryInterface`
- [x] Interface `AttributeRepositoryInterface`

### Application (Use Cases)
- [x] CRUD Categorías
- [x] CRUD Atributos
- [x] CRUD Valores de atributos

### Infrastructure
- [x] Repositorios Eloquent
- [x] Repositorios InMemory
- [x] Modelos
- [x] Migraciones

### Tests
- [x] Tests de repositorios InMemory
- [ ] Tests de entidades
- [ ] Tests de Use Cases

### API/HTTP
- [x] CRUD /taxonomy/categories
- [x] CRUD /taxonomy/attributes
- [x] Rutas de attribute values

### Pendiente
- [ ] Herencia de atributos en jerarquía
- [ ] Atributos requeridos por categoría
- [ ] Validación de valores según tipo de atributo
