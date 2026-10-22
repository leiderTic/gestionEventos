<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cotizacion;
use App\Models\Reserva;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReservaController extends Controller
{
    /**
     * Crear o confirmar la reserva de una cotización (Paso 2).
     */
    public function confirmarReserva(Request $request, $cotizacionId)
    {
        $cotizacion = Cotizacion::with(['documentos', 'servicios'])->findOrFail($cotizacionId);

        $docArrendamiento = $cotizacion->documentos->firstWhere('codigo_tipo', 'RESPUESTA_ARRENDAMIENTO');
        $docServicios = $cotizacion->documentos->firstWhere('codigo_tipo', 'RESPUESTA_SERVICIOS_ADICIONALES');

        if (!$docArrendamiento || empty($docArrendamiento->pdf_firmado_path)) {
            return response()->json([
                'message' => 'Requisito pendiente: Debe generar y subir el PDF firmado de la Respuesta a Solicitud de Arrendamiento antes de confirmar la reserva.'
            ], 422);
        }

        // Solo exigir el documento de Servicios Adicionales si la cotización tiene servicios registrados
        $tieneServicios = $cotizacion->servicios && $cotizacion->servicios->isNotEmpty();
        if ($tieneServicios && (!$docServicios || empty($docServicios->pdf_firmado_path))) {
            return response()->json([
                'message' => 'Requisito pendiente: Debe generar y subir el PDF firmado de la Respuesta a Solicitud de Servicios Adicionales antes de confirmar la reserva.'
            ], 422);
        }

        return DB::transaction(function () use ($request, $cotizacion) {
            $anioActual = date('Y');
            $userId = Auth::id() ?? $cotizacion->user_id;

            // Se captura la fecha de confirmación internamente con la fecha actual
            $fechaReserva = Carbon::now()->toDateString();

            // Si ya existe la reserva, la recuperamos; si no, generamos nuevo correlativo
            $reserva = Reserva::where('cotizacion_id', $cotizacion->id)->first();

            if (!$reserva) {
                $maxCorr = Reserva::whereYear('created_at', $anioActual)->max('correlativo');
                $siguienteCorr = $maxCorr ? $maxCorr + 1 : 1;
                $codigoReserva = 'RES-' . $anioActual . '-' . str_pad($siguienteCorr, 3, '0', STR_PAD_LEFT);

                $reserva = Reserva::create([
                    'cotizacion_id' => $cotizacion->id,
                    'codigo' => $codigoReserva,
                    'correlativo' => $siguienteCorr,
                    'fecha_reserva' => $fechaReserva,
                    'estado' => 'confirmada',
                    'user_id' => $userId,
                    'jefe_ucfchm' => $request->jefe_ucfchm ?? 'Sergio N. Rivera Céspedes',
                    'observaciones' => $request->observaciones ?? null,
                ]);
            } else {
                $reserva->update([
                    'estado' => 'confirmada',
                    'jefe_ucfchm' => $request->jefe_ucfchm ?? $reserva->jefe_ucfchm,
                    'observaciones' => $request->observaciones ?? $reserva->observaciones,
                ]);
            }

            // Asegurar que el expediente avance a Paso 2
            if ($cotizacion->paso < 2) {
                $cotizacion->paso = 2;
                $cotizacion->save();
            }

            return response()->json([
                'message' => 'Reserva confirmada con éxito.',
                'reserva' => $reserva->load('user')
            ], 201);
        });
    }

    /**
     * Generar y emitir el PDF oficial de la Ficha de Reserva (Art. 40).
     */
    public function imprimirFichaReserva($id)
    {
        try {
            app()->setLocale('es');
            $cotModel = Cotizacion::with([
                'user',
                'evento',
                'clientes',
                'tarifas.espacio',
                'pagos.banco',
                'reserva.user'
            ])->findOrFail($id);

            // Si aún no tiene reserva creada, crearla automáticamente para la emisión del documento
            $reserva = $cotModel->reserva;
            if (!$reserva) {
                $anioActual = date('Y');
                $maxCorr = Reserva::whereYear('created_at', $anioActual)->max('correlativo');
                $siguienteCorr = $maxCorr ? $maxCorr + 1 : 1;
                $codigoReserva = 'RES-' . $anioActual . '-' . str_pad($siguienteCorr, 3, '0', STR_PAD_LEFT);

                $reserva = Reserva::create([
                    'cotizacion_id' => $cotModel->id,
                    'codigo' => $codigoReserva,
                    'correlativo' => $siguienteCorr,
                    'fecha_reserva' => Carbon::now()->toDateString(),
                    'estado' => 'confirmada',
                    'user_id' => Auth::id() ?? $cotModel->user_id,
                    'jefe_ucfchm' => 'Sergio N. Rivera Céspedes',
                ]);
            }

            // Clientes
            $clientePrincipal = $cotModel->clientes->firstWhere('tipo_cliente_id', 1) ?? $cotModel->clientes->first();
            $contactoInfo = $cotModel->clientes->firstWhere('tipo_cliente_id', 2) ?? $clientePrincipal;

            $telObj = ($contactoInfo && $contactoInfo->telefono) ? $contactoInfo : $clientePrincipal;
            $tel = $telObj->telefono ?? $telObj->telefono_fijo ?? '';
            if ($tel && !empty($telObj->telefono_codigo)) {
                $tel = '+' . $telObj->telefono_codigo . ' ' . $tel;
            }

            // Fechas del evento en formato DD-MM-YY
            $fIni = Carbon::parse($cotModel->fecha_ini)->format('d-m-y');
            $fFin = Carbon::parse($cotModel->fecha_fin)->format('d-m-y');
            $fechasEvento = $fIni . ' – ' . $fFin;

            // Espacios reservados
            $espaciosNombres = $cotModel->tarifas->map(function ($t) {
                return $t->espacio ? $t->espacio->nombre : null;
            })->filter()->unique()->values()->implode(', ');

            // Pagos y montos
            $montoTotal = (float) $cotModel->monto_total;
            $anticipo20 = round($montoTotal * 0.20, 2);
            $saldo80 = round($montoTotal * 0.80, 2);
            $garantia20 = round($montoTotal * 0.20, 2);

            // Primer pago registrado (anticipo)
            $primerPago = $cotModel->pagos->first();
            $anticipoEstado = $primerPago ? 'Pagado' : 'Pendiente';
            $anticipoComprobante = $primerPago ? ($primerPago->nro_comprobante ?? '-') : '-';

            // Fecha emisión de la reserva
            $fechaReservaCarbon = Carbon::parse($reserva->fecha_reserva);
            $fechaEmisionStr = 'La Paz, ' . $fechaReservaCarbon->locale('es')->translatedFormat('d \d\e F \d\e Y');

            // Logo
            $logoPath = public_path('image/logo.png');
            $logoBase64 = '';
            if (file_exists($logoPath)) {
                $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                $data = file_get_contents($logoPath);
                $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }

            $datos = [
                'codigo_reserva' => $reserva->codigo,
                'codigo_cotizacion' => $cotModel->codigo,
                'fecha_emision' => $fechaEmisionStr,
                'ejecutivo_comercial' => $cotModel->user ? $cotModel->user->name : 'Patricia Castillo Santander',
                'cliente_nombre' => $clientePrincipal ? $clientePrincipal->nombre : 'Sin especificar',
                'nit' => $clientePrincipal ? ($clientePrincipal->ci_nit ?? $clientePrincipal->nit ?? '-') : '-',
                'contacto_nombre' => $contactoInfo ? $contactoInfo->nombre : ($clientePrincipal ? $clientePrincipal->nombre : '-'),
                'telefono' => $tel ?: '-',
                'tipo_evento' => $cotModel->evento ? $cotModel->evento->descripcion : 'Evento Corporativo',
                'fechas_evento' => $fechasEvento,
                'espacios' => $espaciosNombres ?: 'Espacios según cotización',
                'anticipo_monto' => $anticipo20,
                'anticipo_estado' => $anticipoEstado,
                'anticipo_comprobante' => $anticipoComprobante,
                'saldo_monto' => $saldo80,
                'garantia_monto' => $garantia20,
                'total_cotizacion' => $montoTotal,
                'jefe_ucfchm' => $reserva->jefe_ucfchm ?? 'Sergio N. Rivera Céspedes',
            ];

            $pdf = Pdf::loadView('cotizaciones.ficha_reserva_pdf', compact('datos', 'logoBase64'))
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'defaultFont' => 'sans-serif'
                ]);

            $nombreArchivo = 'Ficha_Reserva_' . str_replace('/', '-', $reserva->codigo) . '.pdf';
            return $pdf->stream($nombreArchivo);
        } catch (\Exception $e) {
            \Log::error("Error al generar PDF Ficha de Reserva: " . $e->getMessage());
            return response()->json(['error' => 'Error al generar el PDF de reserva', 'message' => $e->getMessage()], 500);
        }
    }
}
