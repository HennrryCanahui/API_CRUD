# Auditoría Técnica y Documentación Integral del Proyecto API_CRUD
## Rama: `LaravelPassport` (Versión Actualizada)

> **Fecha de Elaboración:** 8 de septiembre de 2026  
> **Ubicación del Proyecto:** `/home/hennrryc/Proyectos/API_CRUD`  
> **Rama Analizada:** `LaravelPassport` (HEAD: commit `4b2261c`)  
> **Ubicación del Informe:** `/home/hennrryc/Proyectos/API_CRUD/log/AUDITORIA_Y_DOCUMENTACION.md`  
> **Tipo de Proyecto:** API RESTful Backend con Autenticación OAuth2 / Personal Access Tokens  
> **Estado:** Sin modificaciones directas al código fuente; auditoría y registro estático exhaustivo.

---

## 1. Resumen Ejecutivo y Evolución del Proyecto

El proyecto **`API_CRUD`** ha evolucionado de un esqueleto inicial con Sanctum a una arquitectura robusta basada en **Laravel Passport 13 (OAuth2)**.

En esta versión (`LaravelPassport`):
1. Se reemplazó por completo **Laravel Sanctum** por **Laravel Passport**, proveyendo un servidor OAuth2 con soporte nativo para *Personal Access Tokens*.
2. Se implementó un controlador de autenticación dedicado ([AuthController.php](file:///home/hennrryc/Proyectos/API_CRUD/app/Http/Controllers/Api/AuthController.php)) que gestiona registro, inicio de sesión y cierre de sesión (revocación de tokens).
3. Se protegió todo el grupo de rutas de tareas (`/api/tasks`) mediante el middleware de guardia `auth:api`.
4. Se incorporó un middleware personalizado ([ForceJsonResponse.php](file:///home/hennrryc/Proyectos/API_CRUD/app/Http/Middleware/ForceJsonResponse.php)) que previene redirecciones HTML ante errores 401 y fuerza respuestas en formato JSON puro.
5. Se añadió una suite de pruebas automatizadas en [tests/Feature/AuthTest.php](file:///home/hennrryc/Proyectos/API_CRUD/tests/Feature/AuthTest.php) (189 líneas) que cubre de forma exhaustiva los flujos de autenticación.

---

## 2. Comparativa Arquitectural: `master` vs `LaravelPassport`

| Componente | Versión Anterior (`master`) | Versión Actual (`LaravelPassport`) |
| :--- | :--- | :--- |
| **Mecanismo de Autenticación** | `laravel/sanctum` (^4.0) | `laravel/passport` (^13.7) |
| **Tablas de Autenticación** | `personal_access_tokens` | 5 tablas OAuth2 (`oauth_clients`, `oauth_access_tokens`, `oauth_refresh_tokens`, `oauth_auth_codes`, `oauth_device_codes`) |
| **Controlador de Autenticación** | Inexistente (solo closure en `routes/api.php`) | [AuthController.php](file:///home/hennrryc/Proyectos/API_CRUD/app/Http/Controllers/Api/AuthController.php) con `register()`, `login()`, `logout()` |
| **Acceso al CRUD de Tareas** | Rutas públicas sin autenticación | Rutas protegidas estrictamente bajo `middleware('auth:api')` |
| **Negociación de Contenido API** | Dependía de headers enviados por el cliente | [ForceJsonResponse.php](file:///home/hennrryc/Proyectos/API_CRUD/app/Http/Middleware/ForceJsonResponse.php) fuerza `Accept: application/json` en todo `/api/*` |
| **Pruebas Automatizadas** | Solo `ExampleTest.php` genérico de Laravel | Suite completa de pruebas de autenticación en [AuthTest.php](file:///home/hennrryc/Proyectos/API_CRUD/tests/Feature/AuthTest.php) |
| **Configuración de Autenticación** | Guard `api` no definido explícitamente en `config/auth.php` | Guard `api` configurado con driver `'passport'` en [config/auth.php](file:///home/hennrryc/Proyectos/API_CRUD/config/auth.php#L46-L49) |

---

## 3. Stack Tecnológico y Dependencias

### 3.1. Entorno de Ejecución
- **PHP:** `8.5.10 (cli)` localmente / `^8.3` definido en `composer.json`.
- **Composer:** `2.10.3`.

### 3.2. Paquetes de Producción (`composer.json`)
```json
"require": {
    "php": "^8.3",
    "laravel/framework": "^13.17",
    "laravel/passport": "^13.7",
    "laravel/tinker": "^3.0"
}
```

### 3.3. Paquetes de Desarrollo
- `phpunit/phpunit`: `^12.5.12`
- `laravel/pint`: `^1.27`
- `laravel/pail`: `^1.2.5`
- `laravel/pao`: `^1.0.6`
- `fakerphp/faker`: `^1.23`
- `mockery/mockery`: `^1.6`
- `nunomaduro/collision`: `^8.6`

### 3.4. Frontend / Bundling
- `vite`: `^8.0.0`
- `tailwindcss`: `^4.0.0`
- `@tailwindcss/vite`: `^4.0.0`

---

## 4. Estructura de Directorios Actualizada

```
API_CRUD/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/
│   │   │   │   ├── AuthController.php   # [NUEVO] Registro, login y logout con Passport
│   │   │   │   └── TaskController.php   # Controlador REST para tareas
│   │   │   └── Controller.php
│   │   ├── Middleware/
│   │   │   └── ForceJsonResponse.php    # [NUEVO] Fuerza cabecera Accept: application/json
│   │   └── Requests/
│   │       ├── StoreTaskRequest.php     # Validación para POST /api/tasks
│   │       └── UpdateTaskRequest.php    # Validación para PUT/PATCH /api/tasks/{id}
│   ├── Models/
│   │   ├── Task.php                     # Modelo Eloquent Task
│   │   └── User.php                     # Modelo User con trait HasApiTokens de Passport
│   └── Providers/
│       └── AppServiceProvider.php
├── bootstrap/
│   └── app.php                          # Inyección de ForceJsonResponse en grupo 'api'
├── config/
│   ├── auth.php                         # Configuración del guard 'api' => 'passport'
│   └── passport.php                     # [NUEVO] Configuración de llaves y conexión de Passport
├── database/
│   ├── factories/
│   │   ├── TaskFactory.php
│   │   └── UserFactory.php
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_cache_table.php
│   │   ├── 0001_01_01_000002_create_jobs_table.php
│   │   ├── 2026_08_22_045854_create_tasks_table.php
│   │   ├── 2026_08_28_110108_create_oauth_auth_codes_table.php       # [OAUTH]
│   │   ├── 2026_08_28_110109_create_oauth_access_tokens_table.php     # [OAUTH]
│   │   ├── 2026_08_28_110110_create_oauth_refresh_tokens_table.php    # [OAUTH]
│   │   ├── 2026_08_28_110111_create_oauth_clients_table.php           # [OAUTH]
│   │   └── 2026_08_28_110112_create_oauth_device_codes_table.php      # [OAUTH]
│   └── seeders/
│       └── DatabaseSeeder.php
├── routes/
│   ├── api.php                          # Rutas públicas y protegidas
│   └── web.php
├── tests/
│   └── Feature/
│       ├── AuthTest.php                 # [NUEVO] 7 casos de prueba para el flujo Auth
│       └── ExampleTest.php
└── log/                                 # Directorio para auditoría y documentación
    └── AUDITORIA_Y_DOCUMENTACION.md
```

---

## 5. Catálogo Completo de Endpoints y Especificación de la API

Todas las rutas se encuentran prefijadas por `/api`.

### 5.1. Endpoints Públicos de Autenticación

#### `POST /api/register`
- **Controlador:** `AuthController@register`
- **Cabeceras:** `Content-Type: application/json`
- **Cuerpo de la Petición:**
  ```json
  {
    "name": "Juan Perez",
    "email": "juan@example.com",
    "password": "password123"
  }
  ```
- **Reglas de Validación:**
  - `name`: `required|string|max:255`
  - `email`: `required|string|email|max:255|unique:users`
  - `password`: `required|string|min:8`
- **Respuestas:**
  - `201 Created`:
    ```json
    {
      "user": {
        "id": 1,
        "name": "Juan Perez",
        "email": "juan@example.com",
        "created_at": "2026-09-08T16:00:00.000000Z",
        "updated_at": "2026-09-08T16:00:00.000000Z"
      },
      "access_token": "def50200... (Bearer JWT Token)",
      "token_type": "Bearer"
    }
    ```
  - `422 Unprocessable Entity`: Errores de validación de campos.

---

#### `POST /api/login`
- **Controlador:** `AuthController@login`
- **Cabeceras:** `Content-Type: application/json`
- **Cuerpo de la Petición:**
  ```json
  {
    "email": "juan@example.com",
    "password": "password123"
  }
  ```
- **Respuestas:**
  - `200 OK`:
    ```json
    {
      "user": { ... },
      "access_token": "def50200...",
      "token_type": "Bearer"
    }
    ```
  - `401 Unauthorized`:
    ```json
    {
      "message": "Unauthorized"
    }
    ```
  - `422 Unprocessable Entity`: Campos requeridos faltantes o mal formados.

---

### 5.2. Endpoints Protegidos (`middleware('auth:api')`)
> **Nota de Seguridad:** Todas las siguientes peticiones deben incluir la cabecera:  
> `Authorization: Bearer <access_token>`

#### `POST /api/logout`
- **Controlador:** `AuthController@logout`
- **Descripción:** Revoca el token de acceso actual en la tabla `oauth_access_tokens`.
- **Respuesta:**
  - `200 OK`:
    ```json
    {
      "message": "Successfully logged out"
    }
    ```

---

#### `GET /api/user`
- **Controlador:** Closure en `routes/api.php`
- **Descripción:** Retorna la información del usuario autenticado.
- **Respuesta:**
  - `200 OK`: Datos del usuario autenticado.
  - `401 Unauthorized`: Si el token no es provisto, es inválido o ha sido revocado.

---

#### `GET /api/tasks`
- **Controlador:** `TaskController@index`
- **Descripción:** Lista todas las tareas existentes en la base de datos.
- **Respuesta:**
  - `200 OK`: Array JSON con la lista de tareas.

---

#### `POST /api/tasks`
- **Controlador:** `TaskController@store`
- **Cuerpo de la Petición:**
  ```json
  {
    "title": "Configurar servidor",
    "description": "Desplegar en VPS Ubuntu",
    "status": "in_progress",
    "priority": "high",
    "due_date": "2026-09-30 18:00:00"
  }
  ```
- **Respuesta:**
  - `201 Created`: Objeto de la tarea recién creada.
  - `422 Unprocessable Entity`: Error de validación según `StoreTaskRequest`.

---

#### `GET /api/tasks/{id}`
- **Controlador:** `TaskController@show`
- **Respuesta:**
  - `200 OK`: Objeto de la tarea encontrada.
  - `404 Not Found`: `{"message": "Task not found"}` si no existe el ID.

---

#### `PUT/PATCH /api/tasks/{id}`
- **Controlador:** `TaskController@update`
- **Cuerpo de la Petición (parcial o completo):**
  ```json
  {
    "status": "completed"
  }
  ```
- **Respuesta:**
  - `200 OK`: Objeto de la tarea actualizado.
  - `404 Not Found`: `{"message": "Task not found"}`.
  - `422 Unprocessable Entity`: Si los datos enviados no cumplen las reglas de `UpdateTaskRequest`.

---

#### `DELETE /api/tasks/{id}`
- **Controlador:** `TaskController@destroy`
- **Respuesta:**
  - `204 No Content`: Eliminación exitosa.
  - `404 Not Found`: `{"message": "Task not found"}`.

---

## 6. Auditoría Técnica Detallada: Hallazgos y Diagnóstico

### 6.1. Mejoras y Hallazgos Resueltos Exitosamente en esta Versión
1. ✅ **Migración a Passport completada:** Se instaló y configuró Passport correctamente en `config/auth.php` (`driver => passport`), `config/passport.php` y en las migraciones de OAuth.
2. ✅ **Emisión de Tokens Operativa:** El modelo `User` ahora utiliza `Laravel\Passport\HasApiTokens` y los métodos `createToken('APIToken')->accessToken` emiten tokens válidos.
3. ✅ **Protección de Endpoints:** Todas las operaciones de tareas están ahora resguardadas tras la capa de autenticación con tokens.
4. ✅ **Resolución de Redirecciones Web Inesperadas:** El middleware `ForceJsonResponse` intercepta todas las peticiones a la API forzando `Accept: application/json`. Cuando una petición no autenticada llega sin cabeceras o con `Accept: text/html`, Laravel no intenta redirigir a una ruta web `/login`, sino que responde con un estricto `401 Unauthenticated.` en JSON.
5. ✅ **Cobertura de Pruebas en Autenticación:** [AuthTest.php](file:///home/hennrryc/Proyectos/API_CRUD/tests/Feature/AuthTest.php) evalúa 7 escenarios críticos, incluyendo creación de clientes de Passport en tiempo de ejecución para evitar bloqueos por prompts interactivos.

---

### 6.2. Hallazgos Nuevos y Puntos Críticos a Resolver

#### 🔴 Hallazgo 1: Trait `HasFactory` comentado en `App\Models\User` (Severidad: Alta)
- **Ubicación:** [app/Models/User.php:L19-L20](file:///home/hennrryc/Proyectos/API_CRUD/app/Models/User.php#L19-L20)
- **Código actual:**
  ```php
  /** @use HasFactory<UserFactory> */
  //use HasFactory, Notifiable;
  use HasApiTokens, Notifiable;
  ```
- **Problema:** Al agregar `use HasApiTokens, Notifiable;`, se comentó la línea que incluía `HasFactory`. 
- **Impacto:** El modelo `User` ya no posee el método `factory()`. Si se ejecuta el seeder principal (`database/seeders/DatabaseSeeder.php:L20`):
  ```php
  User::factory()->create([...]);
  ```
  La aplicación fallará con un error fatal de PHP: `Call to undefined method App\Models\User::factory()`.
- **Solución recomendada:** Unificar los tres traits en una sola directiva:
  ```php
  use HasApiTokens, HasFactory, Notifiable;
  ```

---

#### 🔴 Hallazgo 2: Falta de Relación de Propiedad entre `User` y `Task` (Aislamiento Multi-usuario Inexistente) (Severidad: Alta)
- **Ubicación:** [database/migrations/2026_08_22_045854_create_tasks_table.php](file:///home/hennrryc/Proyectos/API_CRUD/database/migrations/2026_08_22_045854_create_tasks_table.php) y [app/Http/Controllers/Api/TaskController.php](file:///home/hennrryc/Proyectos/API_CRUD/app/Http/Controllers/Api/TaskController.php)
- **Problema:** 
  1. La tabla `tasks` no cuenta con una columna foránea `user_id`.
  2. El modelo `Task` no tiene la relación `belongsTo(User::class)`.
  3. En `TaskController`:
     - `index()` ejecuta `Task::all()`, retornando las tareas de todos los usuarios sin distinción.
     - `store()` guarda la tarea sin asociarla al usuario autenticado (`$request->user()->tasks()->create(...)`).
     - `show()`, `update()` y `destroy()` permiten que **cualquier usuario autenticado consulte, altere o borre tareas creadas por otros usuarios**.
- **Impacto:** Vulnerabilidad de tipo IDOR (*Insecure Direct Object Reference* / Broken Object Level Authorization - OWASP API1:2023). La autenticación es válida, pero la autorización a nivel de recurso no existe.
- **Solución recomendada:** 
  - Agregar columna `foreignId('user_id')->constrained()->cascadeOnDelete();` en la migración de tareas.
  - Relacionar `User hasMany Task` y `Task belongsTo User`.
  - Filtrar las consultas en el controlador para que operen sobre `$request->user()->tasks()`.

---

#### 🟡 Hallazgo 3: Inconsistencia de Nulabilidad en `StoreTaskRequest` (Severidad: Media)
- **Ubicación:** [app/Http/Requests/StoreTaskRequest.php:L28-L29](file:///home/hennrryc/Proyectos/API_CRUD/app/Http/Requests/StoreTaskRequest.php#L28-L29)
- **Problema:** Las reglas declaran:
  ```php
  'status' => 'nullable|in:pending,in_progress,completed',
  'priority' => 'nullable|in:low,medium,high',
  ```
  Mientras que la migración define las columnas como `NOT NULL`:
  ```php
  $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
  $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
  ```
- **Impacto:** Si un cliente envía `{"title": "Demo", "status": null}`, la validación de Laravel da luz verde, pero la base de datos abortará la inserción con un error `SQLSTATE[23000]: Integrity constraint violation: Column 'status' cannot be null` (HTTP 500).
- **Solución recomendada:** Modificar la regla a opcional sin permitir nulos explícitos (ej. no usar `nullable`, sino omitir el campo del payload para que tome el `default` de la base de datos).

---

#### 🟡 Hallazgo 4: Requisito de Claves OAuth y Cliente Personal para Entornos Locales/Producción (Severidad: Media Operativa)
- **Ubicación:** [config/passport.php](file:///home/hennrryc/Proyectos/API_CRUD/config/passport.php)
- **Problema:** En el archivo de pruebas `AuthTest.php`, el cliente de Passport se genera mediante código en el método `setUp()`. Sin embargo, al iniciar la aplicación con `php artisan serve`, si no se han generado los archivos `storage/oauth-private.key` y `storage/oauth-public.key`, ni se ha insertado un *Personal Access Client* en `oauth_clients`, cualquier petición a `/api/register` o `/api/login` fallará lanzando una excepción `PersonalAccessClient not found` o de llaves criptográficas ausentes.
- **Solución recomendada:** Asegurar en la documentación de despliegue la ejecución obligatoria de:
  ```bash
  php artisan passport:install
  ```

---

#### 🟡 Hallazgo 5: Carencia de Paginación en `TaskController::index` (Severidad: Media)
- **Ubicación:** [app/Http/Controllers/Api/TaskController.php:L19](file:///home/hennrryc/Proyectos/API_CRUD/app/Http/Controllers/Api/TaskController.php#L19)
- **Problema:** `Task::all()` carga el 100% de los registros en la memoria del servidor.
- **Solución recomendada:** Implementar paginación con `Task::paginate(15)`.

---

#### 🟡 Hallazgo 6: Ausencia de Route Model Binding y API Resources en `TaskController` (Severidad: Baja / Calidad de Código)
- **Ubicación:** [app/Http/Controllers/Api/TaskController.php](file:///home/hennrryc/Proyectos/API_CRUD/app/Http/Controllers/Api/TaskController.php)
- **Problema:**
  1. Se utiliza `$id` manual con `Task::find($id)` e `if (!$task) return 404` repetido en tres métodos, desaprovechando la inyección `Task $task` de Laravel.
  2. Se retornan modelos Eloquent crudos en lugar de `TaskResource`, exponiendo columnas internas y sin posibilidad de formatear fechas de manera controlada.

---

#### ⚪ Hallazgo 7: Ausencia de Pruebas Específicas para el CRUD de Tareas (Severidad: Informativa)
- **Ubicación:** `tests/Feature/`
- **Problema:** Si bien `AuthTest.php` cubre los flujos de autenticación de forma excelente, no existen pruebas de integración para los métodos de `TaskController` (crear tarea con token válido, rechazar creación sin token, validar 422 en tareas, editar y eliminar).

---

## 7. Guía Paso a Paso para Despliegue y Puesta en Marcha

Para iniciar el proyecto en un entorno local o de evaluación con esta versión de Passport:

1. **Instalar dependencias de Composer:**
   ```bash
   composer install
   ```

2. **Crear archivo `.env`:**
   ```bash
   cp .env.example .env
   ```

3. **Generar la clave de la aplicación:**
   ```bash
   php artisan key:generate
   ```

4. **Crear la base de datos SQLite y ejecutar migraciones:**
   ```bash
   touch database/database.sqlite
   php artisan migrate
   ```

5. **Generar las llaves de encriptación y clientes OAuth de Passport:**
   ```bash
   php artisan passport:install
   ```
   *(Este comando genera `oauth-private.key`, `oauth-public.key` en `storage/` y crea el Personal Access Client necesario para emitir tokens).*

6. **Ejecutar la suite de pruebaclears automatizadas:**
   ```bash
   php artisan test
   ```

7. **Iniciar el servidor de desarrollo:**
   ```bash
   php artisan serve
   ```

---

## 8. Conclusiones y Hoja de Ruta Sugerida

La rama **`LaravelPassport`** representa un salto de calidad importante:
- Sistema de autenticación estándar de la industria (OAuth2 / JWT tokens).
- Manejo impecable de respuestas JSON para APIs sin fugas hacia vistas web.
- Suite de pruebas funcionales para autenticación.

### Pasos recomendados para la siguiente iteración:
1. **Inmediato:** Restaurar `use HasFactory;` en `app/Models/User.php`.
2. **Seguridad / Negocio:** Añadir `user_id` a la tabla `tasks` y limitar el CRUD al usuario autenticado (`$request->user()->tasks()`).
3. **Refactorización:** Adoptar Route Model Binding (`Task $task`) y crear `TaskResource` para el formateo de respuestas JSON.
4. **Testing:** Crear `tests/Feature/TaskTest.php` para asegurar la cobertura del ciclo de vida de las tareas bajo autenticación.
