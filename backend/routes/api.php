<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CotizacionController;
use App\Http\Controllers\EventoController;
use App\Http\Controllers\TemporadaController;
use App\Http\Controllers\EspacioController;
use App\Http\Controllers\BloqueController;
use App\Http\Controllers\TipoEspacioController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\RespaldoController;
use App\Http\Controllers\CrmController;
use App\Http\Controllers\ReunionController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\TarifaController;
use App\Http\Controllers\BancoController;
use App\Http\Controllers\PorcentajeController;
use App\Http\Controllers\TipoRespaldoController;
use App\Http\Controllers\ModalidadController;
use App\Http\Controllers\TipoCrmController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('/v1/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'funLogin']);
    // Registro público deshabilitado: los usuarios los crea el Administrador (/api/users)
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/profile', [AuthController::class, 'funProfile']);   
        Route::post('/logout', [AuthController::class, 'funLogout']);
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    // Clientes
    Route::get('/cliente/instituciones', [ClienteController::class, 'clienteInstituciones']);
    Route::get('/cliente/contactos', [ClienteController::class, 'clienteContactos']);
    Route::get('/cliente/instituciones/{id}/contactos', [ClienteController::class, 'clienteInstitucionesContactos']);
    Route::apiResource('cliente', ClienteController::class);  

    // Eventos y Espacios
    Route::apiResource('eventos', EventoController::class);
    Route::apiResource('temporadas', TemporadaController::class);
    Route::apiResource('espacios', EspacioController::class);
    Route::apiResource('bloques', BloqueController::class);
    Route::apiResource('tipo-espacios', TipoEspacioController::class);

    // Usuarios y Roles (solo Administrador)
    Route::middleware('permission:usuarios.gestionar')->group(function () {
        Route::get('roles', [UserController::class, 'roles']);
        Route::patch('users/{user}/toggle-activo', [UserController::class, 'toggleActivo']);
        Route::apiResource('users', UserController::class);
    });

    // Cotizaciones (lectura filtrada por rol en el controlador)
    Route::get('cotizaciones', [CotizacionController::class, 'index']);
    Route::get('cotizaciones/{cotizacione}', [CotizacionController::class, 'show']);
    Route::middleware('permission:cotizaciones.crear')->group(function () {
        Route::post('cotizaciones/preview', [CotizacionController::class, 'preview']);
        Route::post('cotizaciones', [CotizacionController::class, 'store']);
    });
    Route::middleware('permission:cotizaciones.editar')->group(function () {
        Route::match(['put', 'patch'], 'cotizaciones/{cotizacione}', [CotizacionController::class, 'update']);
        Route::delete('cotizaciones/{cotizacione}', [CotizacionController::class, 'destroy']);
    });

    Route::prefix('cotizaciones/{id}')->group(function () {
        Route::get('imprimir', [CotizacionController::class, 'imprimir']);
        Route::get('ficha-reserva-pdf', [ReservaController::class, 'imprimirFichaReserva']);
        Route::get('respaldos', [RespaldoController::class, 'index']);
        Route::get('crms', [CrmController::class, 'index']);
        Route::get('reuniones', [ReunionController::class, 'index']);

        // Paso 2: Confirmación de reserva (Técnico Comercial)
        Route::middleware('permission:reservas.confirmar')->group(function () {
            Route::post('confirmar-reserva', [ReservaController::class, 'confirmarReserva']);
            Route::post('subir-respaldo', [RespaldoController::class, 'store']);
            Route::post('reuniones', [ReunionController::class, 'store']);
        });

        // Pagos con boucher (Técnico Comercial)
        Route::post('registrar-pago', [PagoController::class, 'registrarPago'])
            ->middleware('permission:pagos.registrar');

        // CRM
        Route::post('crms', [CrmController::class, 'store'])
            ->middleware('permission:cotizaciones.editar');
    });

    // Reuniones — Rutas independientes
    Route::post('reuniones/{id}/acta', [ReunionController::class, 'subirActa'])
        ->middleware('permission:reservas.confirmar');

    // Módulo Administrativo / Pagos
    Route::get('administrativo/pagos-pendientes', [PagoController::class, 'pagosPendientes']);
    Route::put('pagos/{id}/verificar', [PagoController::class, 'verificarPago']);

    // Tarifas y Servicios
    Route::apiResource('servicios', ServicioController::class);
    Route::get('tarifas/espacio/{espacioId}/temporada/{temporadaId}/evento/{eventoId}', [TarifaController::class, 'getByFiltro']);
    Route::get('tarifas/espacio/{espacioId}/temporada/{temporadaId}', [TarifaController::class, 'getByEspacioAndTemporada']);
    Route::get('tarifas/espacio/{espacioId}/baja', [TarifaController::class, 'getBajasByEspacio']);
    Route::get('tarifas/espacio/{espacioId}/alta', [TarifaController::class, 'getAltasByEspacio']);
    Route::get('tarifas/espacio/{espacioId}', [TarifaController::class, 'getByEspacio']);
    Route::apiResource('tarifas', TarifaController::class);

    // Lookups Maestros
    Route::get('bancos', [BancoController::class, 'index']);
    Route::get('porcentajes', [PorcentajeController::class, 'index']);
    Route::get('tipo-respaldos', [TipoRespaldoController::class, 'index']);
    Route::get('modalidades', [ModalidadController::class, 'index']);
    Route::get('tipo-crms', [TipoCrmController::class, 'index']);
});
