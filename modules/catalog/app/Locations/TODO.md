# Locations Module - TODO

## Estado: Funcional

### Dominio
- [x] Entidad `Location`
- [x] Entidad `Address`
- [x] Value Object `LocationType` (warehouse, store, storage_unit, etc)
- [x] Interface `LocationRepository`
- [x] Interface `AddressRepository`
- [x] Soporte jerarquía (parent_id)

### Application (Use Cases)
- [x] CRUD de ubicaciones (via controlador)
- [x] CRUD de direcciones
- [x] Filtros por tipo y parent_id

### Infrastructure
- [x] `EloquentLocationRepository`
- [x] `EloquentAddressRepository`
- [x] `InMemoryLocationRepository`
- [x] `InMemoryAddressRepository`
- [x] `LocationModel`
- [x] `AddressModel`
- [x] Migraciones

### Tests
- [x] `InMemoryLocationRepositoryTest`
- [x] `InMemoryAddressRepositoryTest`
- [ ] Tests de entidades
- [ ] Tests de integración

### API/HTTP
- [x] GET /locations
- [x] GET /locations/{id}
- [x] POST /locations
- [x] PUT /locations/{id}
- [x] DELETE /locations/{id}
- [x] Rutas de addresses

### Pendiente
- [ ] Validación de jerarquía (storage_unit no puede tener hijos)
- [ ] Soft deletes
- [ ] Geocoding para addresses
