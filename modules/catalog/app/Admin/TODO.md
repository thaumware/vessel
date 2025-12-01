# Admin Module - TODO

## Estado: Funcional

### Funcionalidades
- [x] Dashboard con resumen
- [x] Visor de base de datos (tablas del catálogo)
- [x] Ejecutor de tests PHPUnit
- [x] Autenticación (login/logout)
- [x] UI con Tailwind + Alpine.js

### Vistas
- [x] `login.blade.php`
- [x] `dashboard.blade.php`
- [x] Navegación con tabs

### Controlador
- [x] `AdminPanelController`
- [x] Login/logout
- [x] Vista de tablas
- [x] Ejecución de tests

### Rutas
- [x] GET /admin/login
- [x] POST /admin/login
- [x] POST /admin/logout
- [x] GET /admin
- [x] GET /admin/table/{table}
- [x] POST /admin/run-tests

### Tests
- [x] `AdminPanelTest` (Feature)

### Pendiente
- [ ] Edición de registros
- [ ] Filtros en tablas
- [ ] Exportar datos
- [ ] Logs del sistema
