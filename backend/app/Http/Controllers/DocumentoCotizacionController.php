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
     * Mapa de configuración por tipo de documento oficial.
     */
    private const TIPOS_DOCUMENTO = [
        'RESPUESTA_ARRENDAMIENTO' => [
            'vista' => 'documentos.respuesta_arrendamiento',
            'prefijo_archivo' => 'respuesta_arrendamiento',
            'mensaje_exito' => 'Documento de Respuesta a Solicitud de Arrendamiento generado exitosamente.',
            'qr_titulo' => 'RESPUESTA COTIZACION ARRENDAMIENTO',
        ],
        'RESPUESTA_SERVICIOS_ADICIONALES' => [
            'vista' => 'documentos.respuesta_servicios_adicionales',
            'prefijo_archivo' => 'respuesta_servicios_adicionales',
            'mensaje_exito' => 'Documento de Respuesta a Solicitud de Servicios Adicionales generado exitosamente.',
            'qr_titulo' => 'RESPUESTA SERVICIOS ADICIONALES',
        ],
    ];

    /**
     * Resuelve el código interno a partir del slug de ruta o código directo.
     */
    private function resolverCodigoTipo(string $tipo): string
    {
        return match (strtolower($tipo)) {
            'respuesta-arrendamiento', 'respuesta_arrendamiento' => 'RESPUESTA_ARRENDAMIENTO',
            'respuesta-servicios-adicionales', 'respuesta_servicios_adicionales' => 'RESPUESTA_SERVICIOS_ADICIONALES',
            default => abort(404, 'Tipo de documento no soportado.'),
        };
    }

    /**
     * Obtiene los datos iniciales y valores por defecto para el formulario
     * de cualquier documento de respuesta a cotización (Arrendamiento o Servicios Adicionales).
     */
    public function formularioPorTipo($cotizacionId, string $tipo)
    {
        $codigoTipo = $this->resolverCodigoTipo($tipo);

        $cotizacion = Cotizacion::with([
            'evento',
            'clientes.institucion',
            'tarifas.espacio',
            'servicios',
        ])->findOrFail($cotizacionId);

        $plantilla = PlantillaDocumento::where('codigo', $codigoTipo)->first();

        // 1. Destinatario (Persona asociada a la institución, o la persona en sí si no es institución)
        $institucion = $cotizacion->clientes->firstWhere('tipo_cliente_id', 1);
        $contacto = $cotizacion->clientes->firstWhere('tipo_cliente_id', 2);
        $primerCliente = $cotizacion->clientes->first();

        $personaDestinatario = $contacto ?: $primerCliente;
        $destinatarioNombre = $personaDestinatario ? $personaDestinatario->nombre : 'CLIENTE';

        $nombreInstitucion = null;
        if ($institucion) {
            $nombreInstitucion = $institucion->nombre;
        } elseif ($personaDestinatario && $personaDestinatario->institucion) {
            $nombreInstitucion = $personaDestinatario->institucion->nombre;
        }

        $destinatarioCargoInstitucion = $nombreInstitucion
            ? 'ENCARGADO - ' . mb_strtoupper($nombreInstitucion)
            : 'ENCARGADO';

        // 2. Fechas (YYYY-MM-DD para los selectores de fecha y literal para el documento)
        Carbon::setLocale('es');
        $fechaIni = Carbon::parse($cotizacion->fecha_ini);
        $fechaFin = Carbon::parse($cotizacion->fecha_fin);
        $fechaMontaje = $fechaIni->copy()->subDay();
        $fechaDesmontaje = $fechaFin->copy()->addDay();

        $formatoFin = $codigoTipo === 'RESPUESTA_SERVICIOS_ADICIONALES' ? 'd \d\e F \d\e Y' : 'd \d\e F \d\e\l Y';

        if ($fechaIni->equalTo($fechaFin)) {
            $fechasEventoTexto = 'del ' . $fechaIni->translatedFormat($formatoFin);
        } else {
            $fechasEventoTexto = 'del ' . $fechaIni->translatedFormat('d \d\e F') . ' al ' . $fechaFin->translatedFormat($formatoFin);
        }

        $fechaMontajeTexto = $fechaMontaje->translatedFormat('d \d\e F \d\e Y');
        $fechaDesmontajeTexto = $fechaDesmontaje->translatedFormat('d \d\e F \d\e Y');

        // 3. Espacios / Servicios Adicionales
        $espacios = $cotizacion->tarifas
            ->map(fn($t) => $t->espacio ? $t->espacio->nombre : null)
            ->filter()
            ->unique()
            ->values()
            ->implode(', ');

        if ($codigoTipo === 'RESPUESTA_SERVICIOS_ADICIONALES') {
            $serviciosList = $cotizacion->servicios
                ->map(fn($s) => $s->nombre)
                ->filter()
                ->unique()
                ->values()
                ->implode(', ');
            $espacios = $serviciosList ?: ($espacios ?: 'los servicios adicionales solicitados');
        } elseif (empty($espacios)) {
            $espacios = 'las instalaciones solicitadas';
        }

        // 4. Parámetros por defecto de la plantilla
        $params = $plantilla ? ($plantilla->parametros_default ?? []) : [];
        $prefijoCite = $plantilla ? $plantilla->prefijo_cite : 'MEFP/UCPP/ UCFCM/NE N°';

        // 5. Documento previamente guardado (si existe)
        $docExistente = CotizacionDocumento::where('cotizacion_id', $cotizacionId)
            ->where('codigo_tipo', $codigoTipo)
            ->latest()
            ->first();

        $snapshot = $docExistente ? ($docExistente->snapshot_datos ?? []) : [];

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
                'destinatario_nombre' => $snapshot['destinatario_nombre'] ?? $destinatarioNombre,
                'destinatario_cargo_institucion' => $snapshot['destinatario_cargo_institucion'] ?? $destinatarioCargoInstitucion,
                'fecha_evento_ini' => $fechaIni->format('Y-m-d'),
                'fecha_evento_fin' => $fechaFin->format('Y-m-d'),
                'fecha_montaje' => $snapshot['fecha_montaje'] ?? $fechaMontaje->format('Y-m-d'),
                'fecha_desmontaje' => $snapshot['fecha_desmontaje'] ?? $fechaDesmontaje->format('Y-m-d'),
                'fechas_evento_texto' => $fechasEventoTexto,
                'fecha_montaje_texto' => $snapshot['fecha_montaje_texto'] ?? $fechaMontajeTexto,
                'fecha_desmontaje_texto' => $snapshot['fecha_desmontaje_texto'] ?? $fechaDesmontajeTexto,
                'espacios_texto' => $espacios,
                'evento_nombre' => $snapshot['evento_nombre'] ?? ($cotizacion->descripcion ?: ($cotizacion->evento ? $cotizacion->evento->descripcion : 'EVENTO')),
                'prefijo_cite' => $prefijoCite,
            ],
            'documento_guardado' => $docExistente ? [
                'id' => $docExistente->id,
                'pdf_url' => $docExistente->pdf_path ? Storage::url($docExistente->pdf_path) : null,
                'pdf_firmado_url' => $docExistente->pdf_firmado_path ? Storage::url($docExistente->pdf_firmado_path) : null,
                'bloqueado' => (bool) $docExistente->bloqueado,
                'fecha_firma' => $docExistente->fecha_firma ? $docExistente->fecha_firma->format('d/m/Y H:i') : null,
                'created_at' => $docExistente->created_at->format('d/m/Y H:i'),
            ] : null,
        ]);
    }

    /**
     * Alias retrocompatible para Respuesta Arrendamiento.
     */
    public function formularioRespuestaArrendamiento($cotizacionId)
    {
        return $this->formularioPorTipo($cotizacionId, 'RESPUESTA_ARRENDAMIENTO');
    }

    /**
     * Construye los datos necesarios para renderizar la vista Blade del documento.
     */
    private function prepararDatosDocumento(Request $request, Cotizacion $cotizacion, string $codigoTipo = 'RESPUESTA_ARRENDAMIENTO')
    {
        $plantilla = PlantillaDocumento::where('codigo', $codigoTipo)->first();
        $params = $plantilla ? ($plantilla->parametros_default ?? []) : [];
        $prefijoCite = $plantilla ? $plantilla->prefijo_cite : 'MEFP/UCPP/ UCFCM/NE N°';

        Carbon::setLocale('es');
        $fechaDoc = Carbon::parse($request->input('fecha', Carbon::today()));
        $anio = $fechaDoc->format('Y');
        $fechaFormateada = $fechaDoc->translatedFormat('d \d\e F \d\e Y');

        $cite = $request->input('cite', 'S/N');
        $del = $request->input('del');
        $delTexto = $del ? Carbon::parse($del)->translatedFormat('d \d\e F \d\e Y') : null;

        // Fechas del evento tomadas directamente de la cotización en BD (no editables)
        $formatoFin = $codigoTipo === 'RESPUESTA_SERVICIOS_ADICIONALES' ? 'd \d\e F \d\e Y' : 'd \d\e F \d\e\l Y';
        $fechaIniObj = Carbon::parse($cotizacion->fecha_ini);
        $fechaFinObj = Carbon::parse($cotizacion->fecha_fin ?: $cotizacion->fecha_ini);

        if ($fechaIniObj->equalTo($fechaFinObj)) {
            $fechasEventoTexto = 'del ' . $fechaIniObj->translatedFormat($formatoFin);
        } else {
            $fechasEventoTexto = 'del ' . $fechaIniObj->translatedFormat('d \d\e F') . ' al ' . $fechaFinObj->translatedFormat($formatoFin);
        }

        // Fechas de montaje y desmontaje (editables mediante selector de fecha, formateadas a literal)
        $fechaMontajeRaw = $request->input('fecha_montaje');
        $fechaMontajeObj = $fechaMontajeRaw ? Carbon::parse($fechaMontajeRaw) : $fechaIniObj->copy()->subDay();
        $fechaMontajeTexto = $fechaMontajeObj->translatedFormat('d \d\e F \d\e Y');

        $fechaDesmontajeRaw = $request->input('fecha_desmontaje');
        $fechaDesmontajeObj = $fechaDesmontajeRaw ? Carbon::parse($fechaDesmontajeRaw) : $fechaFinObj->copy()->addDay();
        $fechaDesmontajeTexto = $fechaDesmontajeObj->translatedFormat('d \d\e F \d\e Y');

        // Espacios / Servicios Adicionales (calculado directamente de la cotización)
        $espacios = $cotizacion->tarifas
            ->map(fn($t) => $t->espacio ? $t->espacio->nombre : null)
            ->filter()
            ->unique()
            ->values()
            ->implode(', ');

        if ($codigoTipo === 'RESPUESTA_SERVICIOS_ADICIONALES') {
            $serviciosList = $cotizacion->servicios
                ->map(fn($s) => $s->nombre)
                ->filter()
                ->unique()
                ->values()
                ->implode(', ');
            $espacios = $serviciosList ?: ($espacios ?: 'los servicios adicionales solicitados');
        } elseif (empty($espacios)) {
            $espacios = 'las instalaciones solicitadas';
        }

        $qrTitulo = self::TIPOS_DOCUMENTO[$codigoTipo]['qr_titulo'] ?? 'RESPUESTA COTIZACION';
        $eventoNom = $request->input('evento_nombre') ?: ($cotizacion->descripcion ?: ($cotizacion->evento ? $cotizacion->evento->descripcion : 'EVENTO'));
        $qrTexto = "DOC: {$qrTitulo}\nCITE: {$prefijoCite} {$cite}/{$anio}\nEVENTO: {$eventoNom}\nFECHA: {$fechaFormateada}";
        $qrBase64 = base64_encode(QrCode::format('svg')->size(120)->generate($qrTexto));

        $defaultGarantia = $codigoTipo === 'RESPUESTA_SERVICIOS_ADICIONALES' ? 10 : 20;

        return [
            'fecha_formateada' => $fechaFormateada,
            'anio' => $anio,
            'prefijo_cite' => $prefijoCite,
            'cite' => $cite,
            'del_texto' => $delTexto,
            'destinatario_nombre' => $request->input('destinatario_nombre'),
            'destinatario_cargo_institucion' => $request->input('destinatario_cargo_institucion'),
            'evento_nombre' => $request->input('evento_nombre', $cotizacion->descripcion ?: ($cotizacion->evento ? $cotizacion->evento->descripcion : '')),
            'fecha_evento_ini' => $fechaIniObj->format('Y-m-d'),
            'fecha_evento_fin' => $fechaFinObj->format('Y-m-d'),
            'fecha_montaje' => $fechaMontajeObj->format('Y-m-d'),
            'fecha_desmontaje' => $fechaDesmontajeObj->format('Y-m-d'),
            'fechas_evento_texto' => $fechasEventoTexto,
            'fecha_montaje_texto' => $fechaMontajeTexto,
            'fecha_desmontaje_texto' => $fechaDesmontajeTexto,
            'espacios_texto' => $espacios,
            'plazo' => $request->input('plazo', 'tres (3) días hábiles'),
            'porcentaje_anticipo' => $params['porcentaje_anticipo'] ?? 20,
            'cuenta_anticipo_banco' => $params['cuenta_anticipo_banco'] ?? ($params['cuenta_pago_banco'] ?? 'Banco Unión S.A.'),
            'cuenta_anticipo_numero' => $params['cuenta_anticipo_numero'] ?? ($params['cuenta_pago_numero'] ?? '10000041434220'),
            'cuenta_anticipo_titular' => $params['cuenta_anticipo_titular'] ?? ($params['cuenta_pago_titular'] ?? 'MEFP-UCPP-RECURSOS PROPIOS'),
            'plazo_saldo' => $params['plazo_saldo'] ?? 'hasta diez (10) días calendario',
            'porcentaje_saldo' => $params['porcentaje_saldo'] ?? 80,
            'porcentaje_garantia' => $params['porcentaje_garantia'] ?? $defaultGarantia,
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
     * Reglas de validación para la nota de respuesta a cotización.
     */
    private function reglasValidacionRespuesta(): array
    {
        return [
            'fecha' => 'required|date',
            'cite' => 'required|string|max:50',
            'plazo' => 'required|string|max:100',
            'destinatario_nombre' => 'required|string',
            'destinatario_cargo_institucion' => 'required|string',
            'telefono' => 'nullable|string|max:50',
            'correo_electronico' => 'nullable|email|max:100',
            'hoja_ruta' => 'nullable|string|max:100',
            'moscas' => 'nullable|string|max:100',
        ];
    }

    /**
     * Instancia centralizada de DomPDF con opciones institucionales y rutas de fuentes configuradas.
     */
    private function crearInstanciaPdf(string $vista, array $data)
    {
        return Pdf::loadView($vista, $data)
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
    }

    /**
     * Vista previa en PDF por tipo de documento (stream sin guardar en disco).
     */
    public function previewPorTipo(Request $request, $cotizacionId, string $tipo)
    {
        $codigoTipo = $this->resolverCodigoTipo($tipo);
        $config = self::TIPOS_DOCUMENTO[$codigoTipo];

        $request->validate($this->reglasValidacionRespuesta());

        $cotizacion = Cotizacion::with(['evento', 'tarifas.espacio', 'servicios'])->findOrFail($cotizacionId);
        $data = $this->prepararDatosDocumento($request, $cotizacion, $codigoTipo);

        $pdf = $this->crearInstanciaPdf($config['vista'], $data);

        return $pdf->stream("preview_{$config['prefijo_archivo']}_{$cotizacion->id}.pdf");
    }

    /**
     * Alias retrocompatible para preview de Arrendamiento.
     */
    public function previewRespuestaArrendamiento(Request $request, $cotizacionId)
    {
        return $this->previewPorTipo($request, $cotizacionId, 'RESPUESTA_ARRENDAMIENTO');
    }

    /**
     * Guarda los datos y el snapshot en base de datos para cualquier tipo de documento
     * SIN almacenar el PDF sin firmar en disco (el PDF sin firmar se genera al vuelo cuando se requiera).
     * Si el documento ya fue firmado y bloqueado, impide cualquier edición.
     */
    public function guardarPorTipo(Request $request, $cotizacionId, string $tipo)
    {
        $codigoTipo = $this->resolverCodigoTipo($tipo);
        $config = self::TIPOS_DOCUMENTO[$codigoTipo];

        $docExistente = CotizacionDocumento::where('cotizacion_id', $cotizacionId)
            ->where('codigo_tipo', $codigoTipo)
            ->first();

        if ($docExistente && ($docExistente->bloqueado || !empty($docExistente->pdf_firmado_path))) {
            return response()->json([
                'message' => 'Este documento ya cuenta con su versión firmada adjunta y se encuentra bloqueado para edición.',
            ], 422);
        }

        $request->validate($this->reglasValidacionRespuesta());

        $cotizacion = Cotizacion::with(['evento', 'tarifas.espacio', 'servicios'])->findOrFail($cotizacionId);
        $plantilla = PlantillaDocumento::where('codigo', $codigoTipo)->first();
        $data = $this->prepararDatosDocumento($request, $cotizacion, $codigoTipo);

        $doc = CotizacionDocumento::updateOrCreate(
            [
                'cotizacion_id' => $cotizacion->id,
                'codigo_tipo' => $codigoTipo,
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
                'pdf_path' => null,
                'usuario_id' => Auth::id(),
            ]
        );

        return response()->json([
            'message' => $config['mensaje_exito'],
            'documento' => $doc,
        ]);
    }

    /**
     * Sube el documento PDF escaneado con firmas (el único que se almacena en disco)
     * y bloquea la edición del documento.
     */
    public function subirFirmadoPorTipo(Request $request, $cotizacionId, string $tipo)
    {
        $codigoTipo = $this->resolverCodigoTipo($tipo);
        $config = self::TIPOS_DOCUMENTO[$codigoTipo];

        $request->validate([
            'pdf_firmado' => 'required|file|mimes:pdf|max:10240',
        ]);

        $doc = CotizacionDocumento::where('cotizacion_id', $cotizacionId)
            ->where('codigo_tipo', $codigoTipo)
            ->first();

        if (!$doc) {
            return response()->json([
                'message' => 'Primero debe generar y emitir el documento antes de subir la versión firmada.',
            ], 422);
        }

        if ($doc->bloqueado || !empty($doc->pdf_firmado_path)) {
            return response()->json([
                'message' => 'El documento firmado ya fue registrado previamente y se encuentra bloqueado.',
            ], 422);
        }

        $dir = "expedientes/{$cotizacionId}/documentos";
        Storage::disk('public')->makeDirectory($dir);

        $file = $request->file('pdf_firmado');
        $citeLimpio = preg_replace('/[^A-Za-z0-9_-]/', '_', $doc->cite ?? 'SN');
        $fileName = "{$config['prefijo_archivo']}_firmado_{$citeLimpio}_" . time() . ".pdf";
        $filePath = $file->storeAs($dir, $fileName, 'public');

        $doc->update([
            'pdf_firmado_path' => $filePath,
            'fecha_firma' => Carbon::now(),
            'bloqueado' => true,
        ]);

        return response()->json([
            'message' => 'Documento firmado adjuntado con éxito. La edición de esta nota ha quedado bloqueada.',
            'documento' => $doc,
            'pdf_firmado_url' => Storage::url($filePath),
        ]);
    }

    /**
     * Alias retrocompatible para guardar Arrendamiento.
     */
    public function guardarRespuestaArrendamiento(Request $request, $cotizacionId)
    {
        return $this->guardarPorTipo($request, $cotizacionId, 'RESPUESTA_ARRENDAMIENTO');
    }

    /**
     * Genera al vuelo (stream en memoria, sin guardar en disco) el PDF sin firmar
     * a partir de los datos guardados en la base de datos (`snapshot_datos`).
     */
    public function verPdf($cotizacionId, $documentoId)
    {
        $documento = CotizacionDocumento::where('cotizacion_id', $cotizacionId)
            ->findOrFail($documentoId);

        $codigoTipo = $documento->codigo_tipo;
        $config = self::TIPOS_DOCUMENTO[$codigoTipo] ?? self::TIPOS_DOCUMENTO['RESPUESTA_ARRENDAMIENTO'];
        $data = $documento->snapshot_datos ?? [];

        $pdf = $this->crearInstanciaPdf($config['vista'], $data);
        $citeLimpio = preg_replace('/[^A-Za-z0-9_-]/', '_', $documento->cite ?? 'SN');

        return $pdf->stream("{$config['prefijo_archivo']}_{$citeLimpio}.pdf");
    }
}

