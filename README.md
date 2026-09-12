# Proyecto Laravel Passport — API_CRUD (Guías 2 y 3)

Este proyecto implementa un sistema robusto de autenticación y autorización OAuth2 sobre Laravel utilizando **Laravel Passport**:

- **Guía 1**: Instalación de Passport, guard `api`, cliente Client Credentials y protección de rutas.
- **Guía 2**: Password Grant, expiración de tokens, renovación mediante refresh tokens y revocación segura (logout).
- **Guía 3**: Autorización granular con scopes (`scope` y `scopes`), control en controlador con `tokenCan()` y endpoint de depuración.

---

## Flujo de Refresh Token (Guía 2, paso 7)

1. **Login de Usuario (`POST /api/login`)**:
   El usuario envía sus credenciales (`email` y `password`). Tras ser validadas contra el servidor OAuth2 (`/oauth/token` con `grant_type=password`), el servidor devuelve:
   - `access_token`: Token JWT para autorizar peticiones a los endpoints protegidos.
   - `refresh_token`: Token criptográfico de larga duración para renovación.
   - `expires_in`: Tiempo de vida en segundos del `access_token` (configurado en 3600 segundos / 1 hora).

2. **Consumo de la API**:
   El cliente incluye el `access_token` en la cabecera `Authorization: Bearer <access_token>` para acceder a las rutas protegidas (`/api/tasks`, `/api/me`, `/api/productos`).

3. **Expiración del Token**:
   Al transcurrir 1 hora, las peticiones con dicho token devuelven automáticamente `401 Unauthenticated` / `401 Unauthorized`.

4. **Renovación con Refresh Token**:
   El cliente envía una solicitud a `POST /oauth/token` con el cuerpo:
   - `grant_type`: `refresh_token`
   - `refresh_token`: `<refresh_token_guardado>`
   - `client_id`: `<PASSPORT_PASSWORD_CLIENT_ID>`
   - `client_secret`: `<PASSPORT_PASSWORD_CLIENT_SECRET>`

   El servidor valida el refresh token y devuelve un nuevo par de tokens (`access_token` y `refresh_token`).

5. **Invalidación del Token Anterior**:
   El `refresh_token` utilizado queda invalidado inmediatamente y el cliente debe almacenar el nuevo par generado para futuras operaciones.
