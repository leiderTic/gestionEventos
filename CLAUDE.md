# Contexto y Reglas del Proyecto: Gestión de Eventos (CFCHM)

> **Documento de Contexto Compartido para Claude, Agentes de IA y Desarrolladores.**
> Contiene todas las decisiones de arquitectura, comandos frecuentes, integraciones, credenciales de desarrollo y el estado actual del sistema.

---

## 1. Visión General y Repositorio
* **Sistema:** Plataforma comercial y operativa para el Centro de Ferias y Convenciones (CFCHM).
* **Estructura:** Monorepo unificado con Docker Compose (`docker-compose.yml` en la raíz).
  * `backend/`: Laravel 11 (PHP 8.3 + Apache).
  * `frontend/`: Vue 3 + Vite + PrimeVue (Plantilla Sakai).
* **Control de Versiones:** Repositorio Git `https://github.com/leiderTic/gestionEventos.git`.
  * Rama de trabajo diario: `leider`. Rama principal: `main`.

---

## 2. Infraestructura y Contenedores Docker

| Servicio | Contenedor | Imagen / Base | Puerto Host | Descripción / Variables |
| :--- | :--- | :--- | :--- | :--- |
| **Base de Datos** | `gestion_postgres_db` | `postgres:latest` | `5432:5432` | **PostgreSQL** (migrado desde MySQL).<br>DB: `db_events`<br>User: `cfchm_user`<br>Pass: `cfchm_password`<br>PGDATA: `/var/lib/postgresql/data/pgdata` |
| **Backend** | `gestion_backend_laravel` | PHP 8.3 + Apache (`backend/Dockerfile`) | `8081:80` | Incluye `pdo_pgsql`, `gd`, `zip`, `bcmath`.<br>Apache Rewrite habilitado.<br>Directorio de trabajo: `/var/www/html`. |
| **Frontend** | `gestion_frontend_vue` | Node 20 / Vite | `5174:5174` | Servidor de desarrollo Vite con Hot Reload (`CHOKIDAR_USEPOLLING=true`). |

---

## 3. Comandos Habituales de Trabajo (Cheat Sheet)

```bash
# Levantar todos los servicios en segundo plano
docker compose up -d

# Ver estado de los contenedores
docker compose ps

# Ver logs de backend o frontend
docker logs -f gestion_backend_laravel
docker logs -f gestion_frontend_vue

# Ejecutar migraciones y seeders en Laravel
docker exec -it gestion_backend_laravel php artisan migrate:fresh --seed

# Limpiar caché de Laravel
docker exec -it gestion_backend_laravel php artisan optimize:clear

# Reiniciar el frontend tras cambios de configuración
docker restart gestion_frontend_vue
```

---

## 4. Configuración de Red y Conectividad Dinámica (Frontend ↔ Backend)

Para evitar problemas de CORS y permitir que el sistema funcione tanto en desarrollo local como en red local / servidores remotos (ej: `http://10.0.25.66:5174`), **NO se hardcodea la IP ni se depende obligatoriamente de `.env`**:

* **Implementación dinámica en Frontend:**
  * `frontend/src/api/axios.js` y `frontend/src/services/ApiService.js` implementan detección automática del hostname del navegador:
    ```javascript
    const defaultUrl = typeof window !== 'undefined' && window.location 
      ? `http://${window.location.hostname}:8081/api` 
      : 'http://localhost:8081/api';
    baseURL: import.meta.env.VITE_API_URL || defaultUrl;
    ```
  * Si estás en `localhost`, llama a `http://localhost:8081/api`.
  * Si estás accediendo desde otra máquina (ej. `http://10.0.25.66:5174`), automáticamente llama a `http://10.0.25.66:8081/api`.
* **`.gitignore` del Frontend:** Ignora `.env` y `.env.*` para evitar cruces accidentales entre máquinas.

---

## 5. Control de Acceso y Roles (RBAC Spatie)

* **Autenticación:** Laravel Sanctum vía Bearer Token en `POST /api/v1/auth/login`. El payload de login retorna `user`, `roles` y `permissions`.
* **Frontend:** Pinia (`stores/auth.js`), directiva `v-can` (`directives/can.js`) y `meta.permission` en rutas Vue.
* **Usuarios de Prueba (10 usuarios en `UserSeeder.php`):**
  * Formato de Clave: Iniciales en mayúsculas + `2026!` (ej: `LT2026!`).
  * `leider.ticlla` | Rol: `Administrador` | Clave: `LT2026!`
  * `justo.colquehuanca` | Rol: `Tecnico Comercial` | Clave: `JC2026!`
  * `diego.condori` | Rol: `Tecnico Comercial` | Clave: `DC2026!`
  * `valentina.choque` | Rol: `Tecnico Comercial` | Clave: `VC2026!`
  * `santiago.vargas` | Rol: `Tecnico Comercial` | Clave: `SV2026!`
  * `lucia.mendoza` | Rol: `Tecnico UAF` | Clave: `LM2026!`
  * `carlos.mamani` | Rol: `Tecnico UAF` | Clave: `CM2026!`
  * `elena.quispe` | Rol: `Tecnico Legal` | Clave: `EQ2026!`
  * `rodrigo.flores` | Rol: `Tecnico Legal` | Clave: `RF2026!`
  * `andrea.salazar` | Rol: `Tecnico Legal` | Clave: `AS2026!`
* **Reglas de Visibilidad en Cotizaciones (Row-Level Security en `Cotizacion::scopeVisiblePara`):**
  * `Administrador`: Acceso total (`cotizaciones.ver_todas`) y administración de usuarios/roles (`/admin/usuarios`).
  * `Tecnico Comercial`: Solo ve sus propias cotizaciones (`cotizaciones.ver_propias`).
  * `Tecnico UAF`: Solo ve cotizaciones que tienen pagos con boucher (`uaf.ver_con_boucher`) para registrar C21 y factura.
  * `Tecnico Legal`: Solo ve cotizaciones con 100% de pagos completados (`legal.ver_pagadas`) para contratos e informes legales.

---

## 6. Flujo del Módulo Comercial (Reglamento Institucional CFCHM)

El expediente comercial recorre 14 etapas obligatorias:
1. **En gestión (Paso 1):** Creación de cotización (fechas, temporada alta/baja, espacios con tarifas oficiales, servicios adicionales y clientes) junto con los **Documentos Base del Expediente**:
   * `solicitud_arrendamiento_path`: Solicitud de Arrendamiento (formulario/borrador llenado por el cliente con fechas, espacios y servicios requeridos).
   * `verificacion_habilitacion_path`: Formulario de Verificación de Habilitación (certificación administrativa de buen cliente / habilitado).
   * **Almacenamiento Físico:** `storage/app/public/expedientes/{cotizacion_id}/documentos/` expuesto vía symlink `/storage/expedientes/{id}/documentos/...`.
   * **Endpoints:** En creación `POST /api/cotizaciones` (`multipart/form-data`), y en actualización posterior `POST /api/cotizaciones/{id}/documentos-base`.
2. **Confirmación de Reserva (Paso 2 - Art. 40 y 41):** Cierre comercial y bloqueo del espacio.
3. **Reunión Operativa (Paso 3):** Coordinación con montaje y operaciones.
4. **Pago Total + Garantía (Paso 4):** Liquidación del 80% y garantía del 20%.
5. **Informe de Inicio (Paso 5)**
6. **En Revisión Legal (Paso 6)**
7. **Contrato Suscrito (Paso 7)**
8. **Inicio Premontaje (Paso 8)**
9. **Acta de Entrega (Paso 9)**
10. **En Curso (Paso 10)**
11. **Acta de Devolución (Paso 11)**
12. **Informe de Cierre (Paso 12)**
13. **Análisis de Garantía (Paso 13)**
14. **Cerrado (Paso 14)**

---

## 7. Contrato de Integración: Confirmación de Reserva (`CotizacionDetalle.vue`)

Al pulsar el botón **"Confirmar Reserva / Registrar Anticipo"**, el modal ejecuta 3 operaciones:

1. **Respaldo Legal / Comercial (Art. 40):**
   * Endpoint: `POST /api/cotizaciones/{id}/subir-respaldo`
   * Body (`multipart/form-data`):
     * `id_tipo_respaldo`: ID del tipo de respaldo (Carta, Contrato, etc.).
     * `archivo`: Archivo digital (PDF o imagen).
   * Almacenamiento: `storage/app/public/respaldos`.

2. **Anticipo 20% (Pago de Reserva):**
   * Endpoint: `POST /api/cotizaciones/{id}/registrar-pago`
   * Body (`multipart/form-data`):
     * `banco_id`: ID del banco receptor.
     * `nro_comprobante`: Cadena única por banco.
     * `monto_pagado`: Importe depositado (validado con el 20% esperado).
     * `fecha_deposito`: Fecha en formato `YYYY-MM-DD`.
     * `boucher`: Imagen del comprobante (`jpeg,png,jpg`).
   * Estado inicial: `pendiente` (para validación de Tesorería/UAF).

3. **Programación de Reunión Operativa (Art. 41):**
   * Endpoint: `POST /api/cotizaciones/{id}/reuniones`
   * Body:
     * `fecha`: `YYYY-MM-DD`
     * `hora`: `HH:MM`
     * `modalidad_id`: ID de modalidad (Presencial / Virtual).
     * `observaciones`: Texto adicional.
   * Estado: `Programada`.

---

## 8. Mapa de Archivos Clave

* **Backend (`backend/`):**
  * Rutas API: `backend/routes/api.php`
  * Controladores: `backend/app/Http/Controllers/` (`CotizacionController.php`, `PagoController.php`, `RespaldoController.php`, `ReunionController.php`)
  * Modelos: `backend/app/Models/` (`Cotizacion.php`, `Pago.php`, `Respaldo.php`, `Reunion.php`, `User.php`)
  * Vistas Blade PDF: `backend/resources/views/cotizaciones/pdf.blade.php`
  * Seeders: `backend/database/seeders/UserSeeder.php`
* **Frontend (`frontend/`):**
  * Vistas: `frontend/src/views/admin/cotizacion/` (`CotizacionNuevo.vue`, `CotizacionDetalle.vue`, `Cotizacion.vue`)
  * Servicios: `frontend/src/services/` (`cotizacion.services.js`, `pago.services.js`, `banco.services.js`, `tiporespaldo.services.js`)
  * Configuración HTTP: `frontend/src/api/axios.js` y `frontend/src/services/ApiService.js`

---

## 9. Tareas en Curso y Puntos de Atención

1. **Permisos de DomPDF (Generación de PDF en Backend):**
   * Al desplegar en servidores nuevos, DomPDF requiere la carpeta `storage/fonts` y permisos de escritura para `www-data`:
     ```bash
     docker exec -it gestion_backend_laravel mkdir -p storage/fonts
     docker exec -it gestion_backend_laravel chown -R www-data:www-data storage bootstrap/cache
     docker exec -it gestion_backend_laravel chmod -R 777 storage bootstrap/cache
     ```
2. **Cierre del Modal de Confirmación de Reserva:**
   * Crear `frontend/src/services/reunion.services.js` para llamar a `POST /api/cotizaciones/{id}/reuniones`.
   * En `CotizacionDetalle.vue`, al confirmar la reserva, llamar al servicio de reunión y actualizar la cotización a `paso: 2` para avanzar el stepper visual.
