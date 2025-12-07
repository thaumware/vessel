# Domain Entities

## Location
Representa una locación física en el sistema de inventario.

**Propiedades:**
- `id`: Identificador único (string)
- `name`: Nombre de la locación (string)
- `address_id`: Referencia a la dirección (string)
- `type`: Tipo de locación - warehouse, store, office, distribution_center (string)
- `description`: Descripción opcional (string|null)

**Métodos:**
- `getId()`, `getName()`, `getAddressId()`, `getType()`, `getDescription()`
- `toArray()`: Convierte la entidad a array para respuestas API

## Address
Representa una dirección física.

**Propiedades:**
- `id`: Identificador único
- `street`: Calle y número
- `city_id`: Referencia a la ciudad
- `postal_code`: Código postal
- `country`: País

## City
Representa una ciudad.

**Propiedades:**
- `id`: Identificador único
- `name`: Nombre de la ciudad
- `state`: Estado o provincia
- `country`: País

## Warehouse
Entidad especializada que extiende Location para almacenes.

**Propiedades adicionales:**
- `capacity`: Capacidad del almacén
- `temperature_controlled`: Si tiene control de temperatura
