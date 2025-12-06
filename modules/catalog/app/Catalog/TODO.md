# Items Module - TODO

## Estado: Funcional

### Dominio
- [x] Entidad `Item`
- [x] Interface `ItemRepositoryInterface`
- [ ] Value Objects (SKU, etc)

### Application (Use Cases)
- [x] CRUD Items (via controlador)
- [ ] Use Cases separados

### Infrastructure
- [x] `EloquentItemRepository`
- [x] `InMemoryItemRepository`
- [x] `ItemModel`
- [x] Migraciones

### Tests
- [x] `InMemoryItemRepositoryTest`
- [ ] Tests de entidad
- [ ] Tests de Use Cases

### API/HTTP
- [x] GET /items
- [x] GET /items/{id}
- [x] POST /items
- [x] PUT /items/{id}
- [x] DELETE /items/{id}

### Pendiente
- [ ] Validación de SKU único
- [ ] Relación con categorías (Taxonomy)
- [ ] Relación con UoM
- [ ] Variantes de producto
