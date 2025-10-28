# 🛠️ Scripts de Utilidad - Vessel

Este directorio contiene scripts útiles para desarrollo y verificación.

## Disponibles

### `verify.sh` - Verificación de Setup

**Propósito:** Verificar que todo está configurado correctamente antes de levantar servicios.

**Uso:**
```bash
bash scripts/verify.sh
```

**Verifica:**
- ✓ Docker y Docker Compose instalados
- ✓ Puertos 80 y 8080 disponibles
- ✓ Archivos necesarios (.env, docker-compose.yml, módulos)
- ✓ /etc/hosts configurado con dominios locales
- ✓ Red Docker vessel_network existe (o será creada)
- ✓ MySQL accesible desde configuración .env
- ✓ Dockerfiles.dev en cada módulo
- ✓ composer.json en cada módulo

**Salida:**
```
════════════════════════════════════════
Resumen de Verificación
════════════════════════════════════════
✓ Pasadas: 18
✗ Fallos: 0
⚠ Advertencias: 1

✓ ¡Listo para arrancar!
```

---

## Próximos Scripts (Por Implementar)

### `start.sh` - Inicio de Servicios
```bash
bash scripts/start.sh
```
- Ejecuta `docker-compose up -d`
- Espera a que servicios estén listos
- Valida que Traefik enrutador funciona
- Muestra URLs de acceso

### `stop.sh` - Parar Servicios
```bash
bash scripts/stop.sh
```
- Ejecuta `docker-compose down`
- Limpia volúmenes (opcional)

### `logs.sh` - Ver Logs
```bash
bash scripts/logs.sh [servicio]
```
- Sin argumento: logs de todos
- Con argumento: logs de servicio específico

### `shell.sh` - Acceso a Contenedor
```bash
bash scripts/shell.sh [servicio]
```
- Abre bash en contenedor del servicio

### `reset.sh` - Reset Completo
```bash
bash scripts/reset.sh
```
- Para servicios
- Elimina volúmenes
- Limpia caché
- Reinicia limpio

### `test-apis.sh` - Test de APIs
```bash
bash scripts/test-apis.sh
```
- Valida que todas las APIs responden
- Verifica routing de Traefik
- Testea hot-reload

---

## Compatibilidad

| Script | Windows (PowerShell) | Windows (Git Bash) | Mac | Linux |
|--------|---|---|---|---|
| verify.sh | ⚠️ Manual | ✓ | ✓ | ✓ |
| start.sh | ✓ Cmd | ✓ | ✓ | ✓ |
| stop.sh | ✓ Cmd | ✓ | ✓ | ✓ |
| logs.sh | ⚠️ Cmd | ✓ | ✓ | ✓ |
| shell.sh | ✓ Cmd | ✓ | ✓ | ✓ |
| reset.sh | ⚠️ Cmd | ✓ | ✓ | ✓ |
| test-apis.sh | ⚠️ Cmd | ✓ | ✓ | ✓ |

**Nota:** Bash scripts funcionan nativamente en Mac/Linux. Para Windows:
- Usa Git Bash (bundled con Git)
- O usa Windows Subsystem for Linux (WSL2)
- O convierte a PowerShell (archivos `.ps1`)

---

## Alternativas en PowerShell (Windows)

### Verificar setup (PowerShell)
```powershell
# Ver si puertos están disponibles
netstat -ano | findstr :80
netstat -ano | findstr :8080

# Verificar archivos
Test-Path .env
Test-Path docker-compose.yml

# Verificar hosts
Get-Content C:\Windows\System32\drivers\etc\hosts | Select-String "vessel.dev"
```

### Levantando servicios (PowerShell)
```powershell
docker-compose up -d
docker-compose ps
docker-compose logs traefik
```

### Acceso a contenedor (PowerShell)
```powershell
docker-compose exec catalog bash
docker-compose exec catalog powershell  # si prefieres PowerShell
```

---

**Próximo:** Consulta `docs/SETUP.md` para guía de inicio.
