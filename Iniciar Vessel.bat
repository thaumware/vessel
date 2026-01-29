@echo off
setlocal enabledelayedexpansion

echo ============================================
echo   Iniciando Vessel...
echo ============================================
echo.

cd /d "%~dp0"

echo Levantando contenedor Docker...
docker compose up -d core

if errorlevel 1 (
    echo.
    echo ERROR: No se pudo levantar el contenedor.
    echo Verifica que Docker está corriendo.
    pause
    exit /b 1
)

echo.
echo Esperando que el servicio inicie...
timeout /t 3 /nobreak

echo.
echo ============================================
echo   ✓ Vessel está corriendo
echo ============================================
echo.
echo Accede aquí:
echo   - API: http://localhost:8000
echo   - Admin: http://localhost:8000/admin
echo   - Health: http://localhost:8000/health
echo.
echo Abriendo navegador...
start http://localhost:8000

echo.
echo Presiona cualquier tecla para salir...
pause
