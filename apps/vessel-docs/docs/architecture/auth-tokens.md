---
sidebar_position: 1
---

# Guía de Integración: API Tokens

Esta guía describe cómo autenticarse con los servicios de Vessel para integraciones sistema-a-sistema.

## Autenticación

Para acceder a los endpoints privados, debe incluir su token de acceso en el encabezado (header) de cada petición HTTP.

### Header Requerido

```http
VESSEL-ACCESS-PRIVATE: <su-token-de-acceso>
```

> **Nota:** No utilice el header `Authorization` estándar, ya que está reservado para sesiones de usuario.

## Scopes (Permisos)

Su token tiene asignado un nivel de acceso (scope):

*   **`all`**: Acceso administrativo completo a todos los recursos del workspace.
*   **`own`**: Acceso limitado únicamente a los recursos creados por su integración.

## Datos en tiempo real

Todas las respuestas reflejan el estado actual del workspace vinculado al token (stock, reservas externas e internas). Use tokens con scope `all` si su integración crea o libera reservas en nombre de terceros.

## Ejemplo de Uso

### Petición (cURL)

```bash
curl -X GET http://api.vessel.com/api/v1/stock/items/read \
  -H "VESSEL-ACCESS-PRIVATE: sk_live_123456789" \
  -H "Content-Type: application/json"
```

### Respuesta Exitosa (200 OK)

```json
{
  "data": [
    {
      "id": "uuid-...",
      "sku": "ITEM-001",
      "qty": 150
    }
  ]
}
```

## Códigos de Error

| Código | Descripción | Causa Probable |
| :--- | :--- | :--- |
| `401 Unauthorized` | No autorizado | Header faltante o token inválido. |
| `403 Forbidden` | Prohibido | El token es válido pero no tiene permisos (scope) para esta acción. |

## Cómo generar tokens (Admin Panel)

1. Inicia sesión en `/admin` con las credenciales de administrador (Basic Auth).  
2. Ve a la pestaña **Actions** → bloque **API Access Tokens**.  
3. Completa los campos: 
  - `Name` (opcional): etiqueta para identificar el uso (ej. "Webhook ERP").
  - `Scope`: `all` (full access) o `own` (solo recursos propios).
  - `Workspace ID` (opcional): limita el token a un workspace concreto.
4. Presiona **Crear token** y copia el valor mostrado; se muestra una sola vez.

### Revocar un token
- En el mismo bloque, selecciona **Revocar** sobre el token. Se elimina de `auth_access_tokens` y deja de ser válido de inmediato.

### Buenas prácticas
- Trátalo como secreto: no lo compartas ni lo subas a repositorios.
- Rota tokens periódicamente y revoca los que ya no se usan.
- Usa `scope=own` para integraciones de clientes o terceros; deja `scope=all` solo para automatización interna.
- Si tu integración usa múltiples workspaces, crea un token por workspace para aislar permisos.
