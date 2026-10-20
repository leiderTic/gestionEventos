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
  * Controladores: `backend/app/Http/Controllers/` (`CotizacionController.php`, `DocumentoCotizacionController.php`, `PagoController.php`, `RespaldoController.php`, `ReunionController.php`)
  * Modelos: `backend/app/Models/` (`Cotizacion.php`, `CotizacionDocumento.php`, `PlantillaDocumento.php`, `Pago.php`, `Respaldo.php`, `Reunion.php`, `User.php`)
  * Vistas Blade PDF: `backend/resources/views/cotizaciones/pdf.blade.php`, `backend/resources/views/documentos/plantilla_base.blade.php`, `backend/resources/views/documentos/respuesta_arrendamiento.blade.php`, `backend/resources/views/documentos/respuesta_servicios_adicionales.blade.php`
  * Seeders: `backend/database/seeders/UserSeeder.php`, `backend/database/seeders/PlantillaDocumentoSeeder.php`
* **Frontend (`frontend/`):**
  * Vistas: `frontend/src/views/admin/cotizacion/` (`CotizacionNuevo.vue`, `CotizacionDetalle.vue`, `Cotizacion.vue`)
  * Componentes Modulares de Cotización (`frontend/src/components/cotizacion/`):
    * `SeccionDocumentosPaso1.vue`: Tarjetas de presentación de documentos base y notas de respuesta CITE (Arrendamiento y Servicios Adicionales) del Paso 1.
    * `ModalDocumentosBase.vue`: Modal autocontenido para subir/actualizar PDFs de Solicitud de Arrendamiento y Verificación de Habilitación.
    * `ModalRespuestaArrendamiento.vue`: Modal autocontenido y parametrizable por `tipoDocumento` (`respuesta-arrendamiento` | `respuesta-servicios-adicionales`) con formulario reactivo, vista previa PDF y emisión del documento.
    * `ModalVisorPdf.vue`: Visor PDF reutilizable en ventana modal.
  * Servicios: `frontend/src/services/` (`cotizacion.services.js`, `documento.services.js`, `pago.services.js`, `banco.services.js`, `tiporespaldo.services.js`)
  * Configuración HTTP: `frontend/src/api/axios.js` y `frontend/src/services/ApiService.js`

---

## 9. Tareas en Curso y Puntos de Atención

1. **Generación de Códigos de Cotización (`COT-{ALIAS}-NNN/YYYY`):**
   * `CotizacionController::store()` consulta `Cotizacion::withTrashed()` filtrando tanto por `user_id` como por `codigo LIKE "COT-{$alias}-%/{$anioActual}"` y verifica en un bucle `do ... while` la unicidad del código antes de insertar, evitando violaciones del índice único `cotizaciones_codigo_unique` en PostgreSQL incluso cuando existen registros eliminados lógicamente (`SoftDeletes`) o usuarios con las mismas iniciales.
2. **Permisos de DomPDF (Generación de PDF en Backend):**
   * DomPDF requiere la carpeta `storage/fonts` con permisos de escritura para el usuario del servidor web (`www-data`). Al llamar a `setOptions([...])` en Laravel-DomPDF (`DocumentoCotizacionController::crearInstanciaPdf()`), es mandatorio incluir siempre:
     ```php
     'font_dir' => storage_path('fonts'),
     'font_cache' => storage_path('fonts'),
     ```
     pues de lo contrario `setOptions()` sobreescribe la configuración y apunta por defecto al directorio interno `vendor/dompdf/dompdf/lib/fonts` provocando error `fopen() Permission denied`.
   * Comandos de permisos en el contenedor Docker:
     ```bash
     docker exec -it gestion_backend_laravel mkdir -p storage/fonts
     docker exec -it gestion_backend_laravel chown -R www-data:www-data storage bootstrap/cache
     docker exec -it gestion_backend_laravel chmod -R 777 storage bootstrap/cache
     docker exec -it gestion_backend_laravel chmod -R 777 vendor/dompdf/dompdf/lib/fonts
     ```
3. **Cierre del Modal de Confirmación de Reserva:**
   * Crear `frontend/src/services/reunion.services.js` para llamar a `POST /api/cotizaciones/{id}/reuniones`.
   * En `CotizacionDetalle.vue`, al confirmar la reserva, llamar al servicio de reunión y actualizar la cotización a `paso: 2` para avanzar el stepper visual.

---

## 10. Sistema de Plantillas Base Institucionales para Documentos y Cartas (DomPDF)

* **Plantilla Maestra:** `backend/resources/views/documentos/plantilla_base.blade.php`
  * Las cartas o notas hijas extienden esta plantilla usando `@extends('documentos.plantilla_base')` y definen su contenido dentro de `@section('contenido')`.
* **Activos Institucionales:**
  * Imágenes: `backend/public/image/plantilla/`
    * `logo-ucpp.jpg`: Encabezado superior izquierdo (Escudo de Bolivia + UCPP).
    * `fondo-agua.png`: Marca de agua centrada en el fondo (Escudo de Bolivia atenuado).
    * `logo-mefp.png`: Pie de página inferior izquierdo (Ministerio de Economía con línea tricolor).
    * `info-oficina.png`: Pie de página inferior derecho (Chuquiago Marka Oficina Central).
  * Fuentes Tipográficas: `backend/public/fonts/`
    * `CenturyGothic.ttf`, `CenturyGothic-Bold.ttf`, `CenturyGothic-Italic.ttf`, `CenturyGothic-BoldItalic.ttf` (fuentes TrueType que garantizan renderizado perfecto en DomPDF sin caracteres corruptos ni advertencias).
* **Parámetros Oficiales:**
  * Tamaño: Carta (`letter`).
  * Márgenes de página: Superior `3,76 cm`, Inferior `4,01 cm`, Izquierdo `2,54 cm`, Derecho `2,54 cm`.
  * Tipografía: Century Gothic tamaño 11pt.
  * Opciones requeridas en DomPDF: `isPhpEnabled => true` (indispensable para numeración dinámica `Página X de Y`).
* **Arquitectura Modular de Documentos:**
  * **Tipos Soportados (`PlantillaDocumentoSeeder.php`):**
    1. `RESPUESTA_ARRENDAMIENTO` (`respuesta-arrendamiento`): Vista `documentos.respuesta_arrendamiento` (Anticipo 20%, Saldo 80%, Garantía 20%).
    2. `RESPUESTA_SERVICIOS_ADICIONALES` (`respuesta-servicios-adicionales`): Vista `documentos.respuesta_servicios_adicionales` (Pago total 100% en Recursos Propios, Garantía 10% en Fondo Rotativo, glosa `"SERVICIOS ADICIONALES – {EVENTO}"`).
  * **Ciclo de Vida, Almacenamiento e Inmutabilidad (`cotizacion_documentos`):**
    * **PDF sin firmar (Generación al vuelo):** Al guardar el formulario (`POST /api/cotizaciones/{id}/documentos/{tipo}`), solo se persisten los datos y `snapshot_datos` en la base de datos (**no se guarda archivo en disco**). Cada vez que un usuario necesita ver, descargar o imprimir el documento sin firmar (`GET /api/cotizaciones/{id}/documentos/{documentoId}/pdf`), DomPDF lo genera en memoria al instante.
    * **PDF Firmado (Único almacenado en disco):** Cuando el usuario imprime, hace firmar y sube el PDF escaneado (`POST /api/cotizaciones/{id}/documentos/{tipo}/subir-firmado`), el archivo se guarda físicamente en `storage/app/public/expedientes/{cotizacion_id}/documentos/` (junto a la Solicitud de Arrendamiento y la Verificación de Habilitación).
    * **Bloqueo permanente:** Al subir el PDF firmado se registra `pdf_firmado_path`, `fecha_firma` y `bloqueado = true`, impidiendo cualquier edición posterior tanto en Frontend como en Backend (`422`).
    * **Requisito para Confirmar Reserva (Paso 2):** Tanto `CotizacionDetalle.vue` como `ReservaController::confirmarReserva()` exigen que **ambos documentos** (`RESPUESTA_ARRENDAMIENTO` y `RESPUESTA_SERVICIOS_ADICIONALES`) cuenten con su `pdf_firmado_path` subido antes de confirmar la reserva.
  * **Backend (`DocumentoCotizacionController.php`):**
    * Endpoints dinámicos por `{tipo}` (`GET /api/cotizaciones/{id}/documentos/{tipo}/formulario`, `POST .../preview`, `POST .../{tipo}`, `POST .../{tipo}/subir-firmado`).
    * Centraliza las reglas de validación en `reglasValidacionRespuesta()` y la creación de la instancia DomPDF con permisos y fuentes en `crearInstanciaPdf(string $vista, array $data)`.
  * **Servicio Frontend (`frontend/src/services/documento.services.js`):**
    * `subirDocumentosBase(cotizacionId, formData)`
    * `getFormularioPorTipo(cotizacionId, tipo)`
    * `previewPorTipo(cotizacionId, tipo, payload)`
    * `guardarPorTipo(cotizacionId, tipo, payload)`
    * `subirFirmadoPorTipo(cotizacionId, tipo, formData)`
    * `getDocumentoPdf(cotizacionId, documentoId)`
  * **Componentes Vue 3 (`frontend/src/components/cotizacion/`):**
    1. `SeccionDocumentosPaso1.vue`: Muestra las tarjetas de documentos del Paso 1 (Solicitud de Arrendamiento, Verificación de Habilitación, Respuesta de Arrendamiento y Respuesta de Servicios Adicionales con sus CITEs, estado de firma y bloqueo).
    2. `ModalDocumentosBase.vue`: Gestiona selección de archivos PDF, validación de tamaño (máx. 10MB) y subida a `documento.services.js`.
    3. `ModalRespuestaArrendamiento.vue`: Recibe `:tipoDocumento="tipoDocumentoActivo"` para cargar, previsualizar y emitir de forma independiente tanto la nota de Arrendamiento como la de Servicios Adicionales con su propio CITE y datos.
    4. `ModalSubirDocumentoFirmado.vue`: Modal modular para adjuntar el PDF escaneado firmado de cualquiera de las 2 notas y bloquear su edición.
    5. `ModalVisorPdf.vue`: Visor PDF transversal reutilizable para cualquier documento del expediente.


