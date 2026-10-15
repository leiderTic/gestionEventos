<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use App\Models\CotizacionDocumento;
use App\Models\PlantillaDocumento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class DocumentoCotizacionController extends Controller
{
    /**
     * Obtiene los datos iniciales y valores por defecto para el formulario
     * de Respuesta a Solicitud de Cotización por Arrendamiento.
     */
    public function formularioRespuestaArrendamiento($cotizacionId)
    {
        $cotizacion = Cotizacion::with([
            'evento',
            'clientes.institucion',
            'tarifas.espacio',
        ])->findOrFail($cotizacionId);

        $plantilla = PlantillaDocumento::where('codigo', 'RESPUESTA_ARRENDAMIENTO')->first();

        // 1. Destinatario
        $cliente = $cotizacion->clientes->first();
        $destinatarioNombre = $cliente ? $cliente->nombre : 'CLIENTE';
        
        $destinatarioCargoInstitucion = 'ENCARGADO';
        if ($cliente && $cliente->institucion) {
            $destinatarioCargoInstitucion = 'ENCARGADO - ' . mb_strtoupper($cliente->institucion->nombre);
        }

        // 2. Fechas
        Carbon::setLocale('es');
        $fechaIni = Carbon::parse($cotizacion->fecha_ini);
        $fechaFin = Carbon::parse($cotizacion->fecha_fin);
        $fechaMontaje = $fechaIni->copy()->subDay();
        $fechaDesmontaje = $fechaFin->copy()->addDay();

        if ($fechaIni->equalTo($fechaFin)) {
            $fechasEventoTexto = $fechaIni->translatedFormat('d \d\e F \d\e\l Y');
        } else {
            $fechasEventoTexto = 'del ' . $fechaIni->translatedFormat('d \d\e F') . ' al ' . $fechaFin->translatedFormat('d \d\e F \d\e\l Y');
        }

        $fechaMontajeTexto = $fechaMontaje->translatedFormat('d \d\e F \d\e Y');
        $fechaDesmontajeTexto = $fechaDesmontaje->translatedFormat('d \d\e F \d\e Y');

        // 3. Espacios
        $espacios = $cotizacion->tarifas
            ->map(fn($t) => $t->espacio ? $t->espacio->nombre : null)
            ->filter()
            ->unique()
            ->values()
            ->implode(', ');
        if (empty($espacios)) {
            $espacios = 'las instalaciones solicitadas';
        }

        // 4. Parámetros por defecto de la plantilla
        $params = $plantilla ? ($plantilla->parametros_default ?? []) : [];
        $prefijoCite = $plantilla ? $plantilla->prefijo_cite : 'MEFP/UCPP/ UCFCM/NE N°';

        // 5. Documento previamente guardado (si existe)
        $docExistente = CotizacionDocumento::where('cotizacion_id', $cotizacionId)
            ->where('codigo_tipo', 'RESPUESTA_ARRENDAMIENTO')
            ->latest()
            ->first();

        return response()->json([
            'cotizacion' => [
                'id' => $cotizacion->id,
                'codigo' => $cotizacion->codigo,
                'evento_nombre' => $cotizacion->descripcion ?: ($cotizacion->evento ? $cotizacion->evento->descripcion : 'EVENTO'),
            ],
            'valores_defecto' => [
                'fecha' => $docExistente ? $docExistente->fecha->format('Y-m-d') : Carbon::today()->format('Y-m-d'),
                'cite' => $docExistente ? $docExistente->cite : '',
                'del' => $docExistente ? ($docExistente->del ? Carbon::parse($docExistente->del)->format('Y-m-d') : '') : '',
                'plazo' => $docExistente ? $docExistente->plazo : ($params['plazo_anticipo'] ?? 'tres (3) días hábiles'),
                'telefono' => $docExistente ? $docExistente->telefono : ($params['telefono'] ?? '77240445'),
                'correo_electronico' => $docExistente ? $docExistente->correo_electronico : ($params['correo_electronico'] ?? 'heidy.burgoa@economiayfinanzas.gob.bo'),
                'hoja_ruta' => $docExistente ? $docExistente->hoja_ruta : '',
                'moscas' => $docExistente ? $docExistente->moscas : ($params['moscas'] ?? 'RMRR/JLJC/HABB'),
                'destinatario_nombre' => $destinatarioNombre,
                'destinatario_cargo_institucion' => $destinatarioCargoInstitucion,
                'fechas_evento_texto' => $fechasEventoTexto,
                'fecha_montaje_texto' => $fechaMontajeTexto,
                'fecha_desmontaje_texto' => $fechaDesmontajeTexto,
                'espacios_texto' => $espacios,
                'evento_nombre' => $cotizacion->descripcion ?: ($cotizacion->evento ? $cotizacion->evento->descripcion : 'EVENTO'),
                'prefijo_cite' => $prefijoCite,
            ],
            'documento_guardado' => $docExistente ? [
                'id' => $docExistente->id,
                'pdf_url' => $docExistente->pdf_path ? Storage::url($docExistente->pdf_path) : null,
                'created_at' => $docExistente->created_at->format('d/m/Y H:i'),
            ] : null,
        ]);
    }

    /**
     * Construye los datos necesarios para renderizar la vista Blade del documento.
     */
    private function prepararDatosDocumento(Request $request, Cotizacion $cotizacion)
    {
        $plantilla = PlantillaDocumento::where('codigo', 'RESPUESTA_ARRENDAMIENTO')->first();
        $params = $plantilla ? ($plantilla->parametros_default ?? []) : [];
        $prefijoCite = $plantilla ? $plantilla->prefijo_cite : 'MEFP/UCPP/ UCFCM/NE N°';

        Carbon::setLocale('es');
        $fechaDoc = Carbon::parse($request->input('fecha', Carbon::today()));
        $anio = $fechaDoc->format('Y');
        $fechaFormateada = $fechaDoc->translatedFormat('d \d\e F \d\e Y');

        $cite = $request->input('cite', 'S/N');
        $del = $request->input('del');
        $delTexto = $del ? Carbon::parse($del)->translatedFormat('d \d\e F \d\e Y') : null;

        $eventoNom = $request->input('evento_nombre') ?: ($cotizacion->descripcion ?: ($cotizacion->evento ? $cotizacion->evento->descripcion : 'EVENTO'));
        $qrTexto = "DOC: RESPUESTA COTIZACION\nCITE: {$prefijoCite} {$cite}/{$anio}\nEVENTO: {$eventoNom}\nFECHA: {$fechaFormateada}";
        $qrBase64 = base64_encode(QrCode::format('svg')->size(120)->generate($qrTexto));

        return [
            'fecha_formateada' => $fechaFormateada,
            'anio' => $anio,
            'prefijo_cite' => $prefijoCite,
            'cite' => $cite,
            'del_texto' => $delTexto,
            'destinatario_nombre' => $request->input('destinatario_nombre'),
            'destinatario_cargo_institucion' => $request->input('destinatario_cargo_institucion'),
            'evento_nombre' => $request->input('evento_nombre', $cotizacion->descripcion ?: ($cotizacion->evento ? $cotizacion->evento->descripcion : '')),
            'fechas_evento_texto' => $request->input('fechas_evento_texto'),
            'fecha_montaje_texto' => $request->input('fecha_montaje_texto'),
            'fecha_desmontaje_texto' => $request->input('fecha_desmontaje_texto'),
            'espacios_texto' => $request->input('espacios_texto'),
            'plazo' => $request->input('plazo', 'tres (3) días hábiles'),
            'porcentaje_anticipo' => $params['porcentaje_anticipo'] ?? 20,
            'cuenta_anticipo_banco' => $params['cuenta_anticipo_banco'] ?? 'Banco Unión S.A.',
            'cuenta_anticipo_numero' => $params['cuenta_anticipo_numero'] ?? '10000041434220',
            'cuenta_anticipo_titular' => $params['cuenta_anticipo_titular'] ?? 'MEFP-UCPP-RECURSOS PROPIOS',
            'plazo_saldo' => $params['plazo_saldo'] ?? 'hasta diez (10) días calendario',
            'porcentaje_saldo' => $params['porcentaje_saldo'] ?? 80,
            'porcentaje_garantia' => $params['porcentaje_garantia'] ?? 20,
            'cuenta_garantia_banco' => $params['cuenta_garantia_banco'] ?? 'Banco Unión S.A.',
            'cuenta_garantia_numero' => $params['cuenta_garantia_numero'] ?? '10000041433686',
            'cuenta_garantia_titular' => $params['cuenta_garantia_titular'] ?? 'MEFP-UCPP-FONDO ROTATIVO',
            'telefono' => $request->input('telefono', $params['telefono'] ?? '77240445'),
            'correo_electronico' => $request->input('correo_electronico', $params['correo_electronico'] ?? 'heidy.burgoa@economiayfinanzas.gob.bo'),
            'hoja_ruta' => $request->input('hoja_ruta'),
            'moscas' => $request->input('moscas'),
            'qr_base64' => $qrBase64,
        ];
    }

    /**
     * Vista previa en PDF (stream sin guardar en disco).
     */
    public function previewRespuestaArrendamiento(Request $request, $cotizacionId)
    {
        $request->validate([
            'fecha' => 'required|date',
            'cite' => 'required|string|max:50',
            'plazo' => 'required|string|max:100',
            'destinatario_nombre' => 'required|string',
            'destinatario_cargo_institucion' => 'required|string',
        ]);

        $cotizacion = Cotizacion::with(['evento'])->findOrFail($cotizacionId);
        $data = $this->prepararDatosDocumento($request, $cotizacion);

        $pdf = Pdf::loadView('documentos.respuesta_arrendamiento', $data)
            ->setPaper('letter', 'portrait')
            ->setOptions([
                'font_dir' => storage_path('fonts'),
                'font_cache' => storage_path('fonts'),
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'isPhpEnabled' => true,
                'defaultFont' => 'sans-serif',
                'chroot' => [base_path(), public_path(), storage_path()],
            ]);

        return $pdf->stream("preview_respuesta_arrendamiento_{$cotizacion->id}.pdf");
    }

    /**
     * Guarda los datos y genera el PDF definitivo persistido en disco.
     */
    public function guardarRespuestaArrendamiento(Request $request, $cotizacionId)
    {
        $request->validate([
            'fecha' => 'required|date',
            'cite' => 'required|string|max:50',
            'plazo' => 'required|string|max:100',
            'destinatario_nombre' => 'required|string',
            'destinatario_cargo_institucion' => 'required|string',
            'telefono' => 'nullable|string|max:50',
            'correo_electronico' => 'nullable|email|max:100',
            'hoja_ruta' => 'nullable|string|max:100',
            'moscas' => 'nullable|string|max:100',
        ]);

        $cotizacion = Cotizacion::with(['evento'])->findOrFail($cotizacionId);
        $plantilla = PlantillaDocumento::where('codigo', 'RESPUESTA_ARRENDAMIENTO')->first();
        $data = $this->prepararDatosDocumento($request, $cotizacion);

        $pdf = Pdf::loadView('documentos.respuesta_arrendamiento', $data)
            ->setPaper('letter', 'portrait')
            ->setOptions([
                'font_dir' => storage_path('fonts'),
                'font_cache' => storage_path('fonts'),
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'isPhpEnabled' => true,
                'defaultFont' => 'sans-serif',
                'chroot' => [base_path(), public_path(), storage_path()],
            ]);

        $dir = "cotizaciones/{$cotizacion->id}/documentos";
        Storage::disk('public')->makeDirectory($dir);
        $fileName = "respuesta_arrendamiento_{$request->input('cite')}_" . time() . ".pdf";
        $filePath = "{$dir}/{$fileName}";

        Storage::disk('public')->put($filePath, $pdf->output());

        $doc = CotizacionDocumento::updateOrCreate(
            [
                'cotizacion_id' => $cotizacion->id,
                'codigo_tipo' => 'RESPUESTA_ARRENDAMIENTO',
            ],
            [
                'plantilla_id' => $plantilla ? $plantilla->id : null,
                'fecha' => $request->input('fecha'),
                'cite' => $request->input('cite'),
                'del' => $request->input('del'),
                'plazo' => $request->input('plazo'),
                'telefono' => $request->input('telefono'),
                'correo_electronico' => $request->input('correo_electronico'),
                'hoja_ruta' => $request->input('hoja_ruta'),
                'moscas' => $request->input('moscas'),
                'snapshot_datos' => $data,
                'pdf_path' => $filePath,
                'usuario_id' => Auth::id(),
            ]
        );

        return response()->json([
            'message' => 'Documento de Respuesta a Solicitud de Arrendamiento generado exitosamente.',
            'documento' => $doc,
            'pdf_url' => Storage::url($filePath),
        ]);
    }

    /**
     * Muestra o descarga el PDF guardado.
     */
    public function verPdf($cotizacionId, $documentoId)
    {
        $documento = CotizacionDocumento::where('cotizacion_id', $cotizacionId)
            ->findOrFail($documentoId);

        if (!$documento->pdf_path || !Storage::disk('public')->exists($documento->pdf_path)) {
            return response()->json(['message' => 'El archivo PDF no existe en el servidor.'], 404);
        }

        return response()->file(Storage::disk('public')->path($documento->pdf_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($documento->pdf_path) . '"',
        ]);
    }
}
