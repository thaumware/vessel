# Pricing Module - TODO

## Estado: Básico

### Dominio
- [x] Entidad `Price`
- [x] Interface `PriceRepositoryInterface`
- [ ] Value Object `Money`
- [ ] Soporte múltiples monedas

### Application (Use Cases)
- [x] CRUD básico
- [ ] Use Cases separados
- [ ] Historial de precios

### Infrastructure
- [x] Repositorio Eloquent
- [x] Modelo
- [x] Migración

### Tests
- [ ] Tests de entidad
- [ ] Tests de repositorio
- [ ] Tests de Use Cases

### API/HTTP
- [x] CRUD /pricing/prices
- [ ] Consulta de precio vigente

### Pendiente
- [ ] Precios por lista (price lists)
- [ ] Precios por cantidad (volume pricing)
- [ ] Precios por fecha (promociones)
- [ ] Múltiples monedas
