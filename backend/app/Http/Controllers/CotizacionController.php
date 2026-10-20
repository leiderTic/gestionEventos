<?php

namespace App\Http\Controllers;

use App\Models\Cotizacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Evento;
use App\Models\Cliente;
use App\Models\Servicio;
use App\Models\Tarifa;
use App\Models\CotizacionHistorial;

class CotizacionController extends Controller
{
    public function index()
    {
        $query = Cotizacion::with(['user', 'evento', 'clientes', 'servicios', 'tarifas.evento', 'tarifas.espacio'])
            ->visiblePara(Auth::user())
            ->orderBy('created_at', 'desc');

        $cotizaciones = $query->get();
        return response()->json($cotizaciones);
    }

    public function store(Request $request)
    {
        foreach (['clientes', 'servicios', 'tarifas'] as $field) {
            if ($request->has($field) && is_string($request->$field)) {
                $decoded = json_decode($request->$field, true);
                if (is_array($decoded)) {
                    $request->merge([$field => $decoded]);
                }
            }
        }

        $request->validate([
            'descripcion' => 'required|string|max:255',
            'fecha_ini' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_ini',
            'evento_id' => 'required|exists:eventos,id',
            'solicitud_arrendamiento' => [
                'nullable', 'file', 'extensions:pdf', 'max:10240',
                function ($attribute, $value, $fail) {
                    if ($value && $value->isValid()) {
                        $header = @file_get_contents($value->getRealPath(), false, null, 0, 100);
                        if ($header !== false && (str_contains(strtolower($header), '<!doctype') || str_contains(strtolower($header), '<html'))) {
                            $fail('El archivo de Solicitud de Arrendamiento es una página web (HTML) y no un documento PDF válido.');
                        }
                    }
                }
            ],
            'verificacion_habilitacion' => [
                'nullable', 'file', 'extensions:pdf', 'max:10240',
                function ($attribute, $value, $fail) {
                    if ($value && $value->isValid()) {
                        $header = @file_get_contents($value->getRealPath(), false, null, 0, 100);
                        if ($header !== false && (str_contains(strtolower($header), '<!doctype') || str_contains(strtolower($header), '<html'))) {
                            $fail('El archivo de Verificación de Habilitación es una página web (HTML) y no un documento PDF válido.');
                        }
                    }
                }
            ],
            'clientes' => 'nullable|array',
            'clientes.*.id' => 'required|exists:clientes,id',
            'servicios' => 'nullable|array',
            'servicios.*.id' => 'required|exists:servicios,id',
            'servicios.*.cantidad' => 'required|numeric|min:0',
            'servicios.*.dias' => 'required|numeric|min:1',
            'servicios.*.precio_aplicado' => 'required|numeric|min:0',
            'tarifas' => 'nullable|array',
            'tarifas.*.id' => 'required|exists:tarifas,id',
            'tarifas.*.dias' => 'required|numeric|min:0',
            'tarifas.*.precio_aplicado' => 'required|numeric|min:0',
        ], [
            'solicitud_arrendamiento.extensions' => 'El archivo de Solicitud de Arrendamiento debe ser de formato PDF (.pdf).',
            'solicitud_arrendamiento.max' => 'La Solicitud de Arrendamiento no puede superar los 10 MB.',
            'verificacion_habilitacion.extensions' => 'El archivo de Verificación de Habilitación debe ser de formato PDF (.pdf).',
            'verificacion_habilitacion.max' => 'La Verificación de Habilitación no puede superar los 10 MB.',
        ]);

        return DB::transaction(function () use ($request) {
            $userId = Auth::id() ?? 1;
            $user = User::find($userId);
            $alias = $user && !empty($user->alias) ? $user->alias : 'XX';
            $anioActual = date('Y');

            // 1. Obtener correlativo máximo considerando también cotizaciones eliminadas (withTrashed)
            // y cualquier cotización con el mismo alias para evitar colisiones globales
            $maxCorrelativo = Cotizacion::withTrashed()
                ->where(function ($q) use ($userId, $alias, $anioActual) {
                    $q->where('user_id', $userId)
                      ->orWhere('codigo', 'LIKE', "COT-{$alias}-%/{$anioActual}");
                })
                ->whereYear('created_at', $anioActual)
                ->max('correlativo');

            $siguienteCorrelativo = $maxCorrelativo ? ((int) $maxCorrelativo + 1) : 1;

            // 2. Garantizar que el código generado sea único en la base de datos (incluso trashed)
            do {
                $correlativoFormateado = str_pad($siguienteCorrelativo, 3, '0', STR_PAD_LEFT);
                $codigo = 'COT-' . $alias . '-' . $correlativoFormateado . '/' . $anioActual;
                $existe = Cotizacion::withTrashed()->where('codigo', $codigo)->exists();
                if ($existe) {
                    $siguienteCorrelativo++;
                }
            } while ($existe);

            $cotizacion = Cotizacion::create([
                'codigo' => $codigo,
                'correlativo' => $siguienteCorrelativo,
                'descripcion' => $request->descripcion,
                'fecha_ini' => $request->fecha_ini,
                'fecha_fin' => $request->fecha_fin,
                'paso' => 1,
                'user_id' => $userId,
                'evento_id' => $request->evento_id,
            ]);

            $updates = [];
            if ($request->hasFile('solicitud_arrendamiento')) {
                $ext = strtolower($request->file('solicitud_arrendamiento')->getClientOriginalExtension() ?: 'pdf');
                $filename = \Illuminate\Support\Str::random(40) . '.' . $ext;
                $updates['solicitud_arrendamiento_path'] = $request->file('solicitud_arrendamiento')
                    ->storeAs("expedientes/{$cotizacion->id}/documentos", $filename, 'public');
            }

            if ($request->hasFile('verificacion_habilitacion')) {
                $ext = strtolower($request->file('verificacion_habilitacion')->getClientOriginalExtension() ?: 'pdf');
                $filename = \Illuminate\Support\Str::random(40) . '.' . $ext;
                $updates['verificacion_habilitacion_path'] = $request->file('verificacion_habilitacion')
                    ->storeAs("expedientes/{$cotizacion->id}/documentos", $filename, 'public');
            }

            if (!empty($updates)) {
                $cotizacion->update($updates);
            }

            if ($request->has('clientes')) {
                $syncData = [];
                foreach ($request->clientes as $cliente) {
                    if (isset($cliente['id']) && $cliente['id']) {
                        $syncData[$cliente['id']] = ['estado' => true];
                    }
                }
                $cotizacion->clientes()->sync($syncData);
            }

            if ($request->has('servicios')) {
                $syncData = [];
                foreach ($request->servicios as $servicio) {
                    $syncData[$servicio['id']] = [
                        'cantidad' => $servicio['cantidad'],
                        'dias' => $servicio['dias'],
                        'precio_aplicado' => $servicio['precio_aplicado'],
                        'estado' => true
                    ];
                }
                $cotizacion->servicios()->sync($syncData);
            }

            if ($request->has('tarifas')) {
                $syncData = [];
                foreach ($request->tarifas as $tarifa) {
                    $syncData[$tarifa['id']] = [
                        'dias' => $tarifa['dias'],
                        'precio_aplicado' => $tarifa['precio_aplicado'],
                        'estado' => true
                    ];
                }
                $cotizacion->tarifas()->sync($syncData);
            }

            // Recalcular totales después de sincronizar relaciones
            $cotizacion->refreshTotals();

            return response()->json($cotizacion->load(['clientes', 'servicios', 'tarifas']), 201);
        });
    }

    public function show(string $id)
    {
        $cotizacion = Cotizacion::with([
            'user',
            'evento',
            'clientes.institucion',
            'servicios',
            'tarifas.evento',
            'tarifas.espacio',
            'pagos.banco',
            'respaldos.tipoRespaldo',
            'reuniones.modalidad',
            'reserva.user',
            'documentos'
        ])->visiblePara(Auth::user())->find($id);

        if (!$cotizacion) {
            abort(Cotizacion::whereKey($id)->exists() ? 403 : 404, 'No tiene acceso a esta cotización.');
        }
        return response()->json($cotizacion);
    }

    public function update(Request $request, string $id)
    {
        $cotizacion = Cotizacion::findOrFail($id);

        $request->validate([
            'descripcion' => 'sometimes|required|string|max:255',
            'fecha_ini' => 'sometimes|required|date',
            'fecha_fin' => 'sometimes|required|date|after_or_equal:fecha_ini',
            'evento_id' => 'sometimes|required|exists:eventos,id',
            'paso' => 'sometimes|integer',
            'vencido' => 'sometimes|boolean',
            'motivo' => 'nullable|string|min:3'
        ]);

        return DB::transaction(function () use ($request, $cotizacion) {
            // Guardamos los cambios realizados (opcionalmente)
            $oldData = $cotizacion->toArray();
            
            $cotizacion->update($request->only(['descripcion', 'fecha_ini', 'fecha_fin', 'paso', 'vencido', 'evento_id']));

            if ($request->has('clientes')) {
                $syncData = [];
                foreach ($request->clientes as $cliente) {
                    $syncData[$cliente['id']] = ['estado' => true];
                }
                $cotizacion->clientes()->sync($syncData);
            }

            if ($request->has('servicios')) {
                $syncData = [];
                foreach ($request->servicios as $servicio) {
                    $syncData[$servicio['id']] = [
                        'cantidad' => $servicio['cantidad'],
                        'precio_aplicado' => $servicio['precio_aplicado'],
                        'estado' => $servicio['estado'] ?? true
                    ];
                }
                $cotizacion->servicios()->sync($syncData);
            }

            if ($request->has('tarifas')) {
                $syncData = [];
                foreach ($request->tarifas as $tarifa) {
                    $syncData[$tarifa['id']] = [
                        'dias' => $tarifa['dias'],
                        'precio_aplicado' => $tarifa['precio_aplicado'],
                        'estado' => $tarifa['estado'] ?? true
                    ];
                }
                $cotizacion->tarifas()->sync($syncData);
            }

            // Recalcular totales después de sincronizar relaciones
            $cotizacion->refreshTotals();

            // Registrar en el historial
            CotizacionHistorial::create([
                'cotizacion_id' => $cotizacion->id,
                'user_id' => Auth::id() ?? 1, // Fallback si no hay sesión para pruebas
                'accion' => 'actualizacion',
                'motivo' => $request->motivo ?? ('Actualización a paso ' . ($request->paso ?? $cotizacion->paso)),
                'cambios' => [
                    'antes' => $oldData,
                    'despues' => $cotizacion->fresh()->toArray()
                ]
            ]);

            return response()->json($cotizacion->load(['clientes', 'servicios', 'tarifas', 'historial.user']));
        });
    }

    public function destroy(string $id)
    {
        $cotizacion = Cotizacion::findOrFail($id);
        $cotizacion->delete();
        return response()->json(['message' => 'Cotización eliminada correctamente']);
    }

    public function preview(Request $request)
    {
        try {
            app()->setLocale('es');
            $cotizacion = $request->all();
            
            if(!isset($cotizacion['codigo'])) {
                $cotizacion['codigo'] = 'NO REGISTRADO - VISTA PREVIA';
                $cotizacion['is_preview'] = true;
            }

            // 1. Hidratar Evento
            $evento = Evento::find($cotizacion['evento_id'] ?? null);
            $cotizacion['tipo_evento_nombre'] = $evento ? $evento->descripcion : 'Evento Desconocido';
            
            // Rango de fechas
            $fIni = isset($cotizacion['fecha_ini']) ? Carbon::parse($cotizacion['fecha_ini'])->translatedFormat('d \d\e F \d\e Y') : date('d \d\e F \d\e Y');
            $fFin = isset($cotizacion['fecha_fin']) ? Carbon::parse($cotizacion['fecha_fin'])->translatedFormat('d \d\e F \d\e Y') : date('d \d\e F \d\e Y');
            $cotizacion['fecha_rango'] = $fIni . ' al ' . $fFin;
            $cotizacion['temporada_label'] = 'Vista Previa';

            // 2. Hidratar Clientes (Buscar temporal o numérico)
            $clientePrincipal = null;
            $contactoInfo = null;

            if (!empty($cotizacion['clientes'])) {
                $cData = $cotizacion['clientes'][0];
                if (isset($cData['id']) && is_numeric($cData['id'])) {
                    $clientePrincipal = Cliente::find($cData['id']);
                } else {
                    $clientePrincipal = (object) $cData; // Soporte para clientes inyectados en vista previa
                }
                
                if (isset($cotizacion['clientes'][1])) {
                    $contData = $cotizacion['clientes'][1];
                    if (isset($contData['id']) && is_numeric($contData['id'])) {
                        $contactoInfo = Cliente::find($contData['id']);
                    } else {
                        $contactoInfo = (object) $contData;
                    }
                } else {
                    $contactoInfo = $clientePrincipal;
                }
            }

            if ($clientePrincipal && isset($clientePrincipal->nombre)) {
                $cotizacion['entidad_nombre'] = $clientePrincipal->nombre;
                $cotizacion['nit'] = $clientePrincipal->ci_nit ?? $clientePrincipal->nit ?? '-';
                
                // Buscar telefono en el contacto o en el cliente
                $telObj = (isset($contactoInfo->telefono) && $contactoInfo->telefono) ? $contactoInfo : $clientePrincipal;
                $tel = $telObj->telefono ?? $telObj->telefono_fijo ?? '';
                if ($tel && !empty($telObj->telefono_codigo)) {
                    $tel = '+' . $telObj->telefono_codigo . ' ' . $tel;
                } elseif ($tel && is_numeric($telObj->id ?? '')) {
                    $tel = '+591 ' . $tel; // Fallback
                }
                $cotizacion['telefono'] = $tel ?: '-';
                
                $cotizacion['contacto_nombre'] = $contactoInfo->nombre ?? 'Contacto';
            } else {
                $cotizacion['entidad_nombre'] = 'Cliente (En borrador)';
                $cotizacion['nit'] = '-';
                $cotizacion['telefono'] = '-';
                $cotizacion['contacto_nombre'] = 'Contacto (En borrador)';
            }

            // 3. Hidratar Espacios a partir de Tarifas
            $totalDias = 0;
            $subtotalEspacios = 0;
            $espaciosMapeados = [];
            if (!empty($cotizacion['tarifas'])) {
                foreach ($cotizacion['tarifas'] as $tarifa) {
                    $tarifaModel = Tarifa::with('espacio')->find($tarifa['id'] ?? null);
                    $nombreEspacio = ($tarifaModel && $tarifaModel->espacio) ? $tarifaModel->espacio->nombre : 'Espacio Borrador';
                    
                    $dias = floatval($tarifa['dias'] ?? 1);
                    $precio = floatval($tarifa['precio_aplicado'] ?? 0);
                    $sub = $dias * $precio;
                    $subtotalEspacios += $sub;
                    if ($dias > $totalDias) $totalDias = $dias;

                    $espaciosMapeados[] = [
                        'nombre' => $nombreEspacio,
                        'precio_dia' => $precio,
                        'dias' => $dias,
                        'subtotal' => $sub
                    ];
                }
            }
            $cotizacion['espacios'] = $espaciosMapeados;
            $cotizacion['total_dias'] = $totalDias > 0 ? $totalDias : 1;
            $cotizacion['subtotal_espacios'] = $subtotalEspacios;

            // 4. Hidratar Servicios
            $subtotalServicios = 0;
            $serviciosMapeados = [];
            if (!empty($cotizacion['servicios'])) {
                foreach ($cotizacion['servicios'] as $servicio) {
                    $servicioModel = Servicio::find($servicio['id'] ?? null);
                    $nombreServicio = $servicioModel ? $servicioModel->nombre : 'Servicio Borrador';
                    
                    $cantidad = floatval($servicio['cantidad'] ?? 1);
                    $dias = floatval($servicio['dias'] ?? 1);
                    $precio = floatval($servicio['precio_aplicado'] ?? 0);
                    $sub = $cantidad * $dias * $precio;
                    $subtotalServicios += $sub;

                    $serviciosMapeados[] = [
                        'nombre' => $nombreServicio,
                        'cantidad' => $cantidad,
                        'dias' => $dias,
                        'precio' => $precio,
                        'subtotal' => $sub
                    ];
                }
            }
            $cotizacion['servicios'] = $serviciosMapeados;
            $cotizacion['subtotal_servicios'] = $subtotalServicios;
            $cotizacion['total'] = $subtotalEspacios + $subtotalServicios;
            
            // Cargar Logo como Base64 para máxima compatibilidad con DomPDF
            $logoPath = public_path('image/logo.png');
            $logoBase64 = '';
            if (file_exists($logoPath)) {
                $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                $data = file_get_contents($logoPath);
                $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }

            // Configuración para optimizar rendimiento y compatibilidad
            $pdf = Pdf::loadView('cotizaciones.pdf', compact('cotizacion', 'logoBase64'))
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'defaultFont' => 'sans-serif'
                ]);

            return $pdf->stream('preview.pdf');
        } catch (\Exception $e) {
            \Log::error("Error en PDF Preview: " . $e->getMessage());
            return response()->json([
                'error' => 'Error al generar el PDF',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function imprimir($id)
    {
        try {
            app()->setLocale('es');
            $cotModel = Cotizacion::with(['evento', 'clientes', 'servicios', 'tarifas.espacio'])->findOrFail($id);
            
            $cotizacion = [
                'codigo' => $cotModel->codigo,
                'is_preview' => false,
                'tipo_evento_nombre' => $cotModel->evento ? $cotModel->evento->descripcion : 'Evento Desconocido',
                'temporada_label' => 'Cotización Oficial',
                'descripcion' => $cotModel->descripcion,
            ];
            
            $fIni = Carbon::parse($cotModel->fecha_ini)->translatedFormat('d \d\e F \d\e Y');
            $fFin = Carbon::parse($cotModel->fecha_fin)->translatedFormat('d \d\e F \d\e Y');
            $cotizacion['fecha_rango'] = $fIni . ' al ' . $fFin;
            
            $clientePrincipal = $cotModel->clientes->firstWhere('tipo_cliente_id', 1) ?? $cotModel->clientes->first();
            $contactoInfo = $cotModel->clientes->firstWhere('tipo_cliente_id', 2) ?? $clientePrincipal;
            
            if ($clientePrincipal) {
                $cotizacion['entidad_nombre'] = $clientePrincipal->nombre;
                $cotizacion['nit'] = $clientePrincipal->ci_nit ?? $clientePrincipal->nit ?? '-';
                
                $telObj = ($contactoInfo && $contactoInfo->telefono) ? $contactoInfo : $clientePrincipal;
                $tel = $telObj->telefono ?? $telObj->telefono_fijo ?? '';
                if ($tel && !empty($telObj->telefono_codigo)) {
                    $tel = '+' . $telObj->telefono_codigo . ' ' . $tel;
                }
                $cotizacion['telefono'] = $tel ?: '-';
                $cotizacion['contacto_nombre'] = $contactoInfo ? $contactoInfo->nombre : 'Contacto';
            }
            
            $totalDias = 0;
            $espaciosMapeados = [];
            foreach ($cotModel->tarifas as $tarifa) {
                $dias = floatval($tarifa->pivot->dias ?? 1);
                $precio = floatval($tarifa->pivot->precio_aplicado ?? 0);
                $sub = $dias * $precio;
                if ($dias > $totalDias) $totalDias = $dias;

                $espaciosMapeados[] = [
                    'nombre' => $tarifa->espacio ? $tarifa->espacio->nombre : 'Espacio',
                    'precio_dia' => $precio,
                    'dias' => $dias,
                    'subtotal' => $sub
                ];
            }
            $cotizacion['espacios'] = $espaciosMapeados;
            $cotizacion['total_dias'] = $totalDias > 0 ? $totalDias : 1;
            $cotizacion['subtotal_espacios'] = $cotModel->monto_tarifas;
            
            $serviciosMapeados = [];
            foreach ($cotModel->servicios as $servicio) {
                $cantidad = floatval($servicio->pivot->cantidad ?? 1);
                $dias = floatval($servicio->pivot->dias ?? 1);
                $precio = floatval($servicio->pivot->precio_aplicado ?? 0);
                $sub = $cantidad * $dias * $precio;

                $serviciosMapeados[] = [
                    'nombre' => $servicio->nombre,
                    'cantidad' => $cantidad,
                    'dias' => $dias,
                    'precio' => $precio,
                    'subtotal' => $sub
                ];
            }
            $cotizacion['servicios'] = $serviciosMapeados;
            $cotizacion['subtotal_servicios'] = $cotModel->monto_servicios;
            $cotizacion['total'] = $cotModel->monto_total;
            
            $logoPath = public_path('image/logo.png');
            $logoBase64 = '';
            if (file_exists($logoPath)) {
                $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                $data = file_get_contents($logoPath);
                $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }

            $pdf = Pdf::loadView('cotizaciones.pdf', compact('cotizacion', 'logoBase64'))
                ->setPaper('a4', 'portrait')
                ->setOptions([
                    'isHtml5ParserEnabled' => true,
                    'isRemoteEnabled' => true,
                    'defaultFont' => 'sans-serif'
                ]);

            // Reemplazar la barra inclinada '/' por un guion '-' para el nombre del archivo
            $nombreArchivo = str_replace('/', '-', $cotModel->codigo) . '.pdf';
            return $pdf->stream($nombreArchivo);
        } catch (\Exception $e) {
            \Log::error("Error en PDF Imprimir: " . $e->getMessage());
            return response()->json(['error' => 'Error al generar el PDF', 'message' => $e->getMessage()], 500);
        }
    }

    public function subirDocumentosBase(Request $request, string $id)
    {
        $cotizacion = Cotizacion::visiblePara(Auth::user())->findOrFail($id);

        $request->validate([
            'solicitud_arrendamiento' => [
                'nullable', 'file', 'extensions:pdf', 'max:10240',
                function ($attribute, $value, $fail) {
                    if ($value && $value->isValid()) {
                        $header = @file_get_contents($value->getRealPath(), false, null, 0, 100);
                        if ($header !== false && (str_contains(strtolower($header), '<!doctype') || str_contains(strtolower($header), '<html'))) {
                            $fail('El archivo de Solicitud de Arrendamiento es una página web (HTML) y no un documento PDF válido.');
                        }
                    }
                }
            ],
            'verificacion_habilitacion' => [
                'nullable', 'file', 'extensions:pdf', 'max:10240',
                function ($attribute, $value, $fail) {
                    if ($value && $value->isValid()) {
                        $header = @file_get_contents($value->getRealPath(), false, null, 0, 100);
                        if ($header !== false && (str_contains(strtolower($header), '<!doctype') || str_contains(strtolower($header), '<html'))) {
                            $fail('El archivo de Verificación de Habilitación es una página web (HTML) y no un documento PDF válido.');
                        }
                    }
                }
            ],
        ], [
            'solicitud_arrendamiento.extensions' => 'El archivo de Solicitud de Arrendamiento debe ser de formato PDF (.pdf).',
            'solicitud_arrendamiento.max' => 'La Solicitud de Arrendamiento no puede superar los 10 MB.',
            'verificacion_habilitacion.extensions' => 'El archivo de Verificación de Habilitación debe ser de formato PDF (.pdf).',
            'verificacion_habilitacion.max' => 'La Verificación de Habilitación no puede superar los 10 MB.',
        ]);

        $updates = [];
        if ($request->hasFile('solicitud_arrendamiento')) {
            $ext = strtolower($request->file('solicitud_arrendamiento')->getClientOriginalExtension() ?: 'pdf');
            $filename = \Illuminate\Support\Str::random(40) . '.' . $ext;
            $updates['solicitud_arrendamiento_path'] = $request->file('solicitud_arrendamiento')
                ->storeAs("expedientes/{$cotizacion->id}/documentos", $filename, 'public');
        }

        if ($request->hasFile('verificacion_habilitacion')) {
            $ext = strtolower($request->file('verificacion_habilitacion')->getClientOriginalExtension() ?: 'pdf');
            $filename = \Illuminate\Support\Str::random(40) . '.' . $ext;
            $updates['verificacion_habilitacion_path'] = $request->file('verificacion_habilitacion')
                ->storeAs("expedientes/{$cotizacion->id}/documentos", $filename, 'public');
        }

        if (!empty($updates)) {
            $cotizacion->update($updates);
        }

        return response()->json([
            'message' => 'Documentos base actualizados exitosamente.',
            'solicitud_arrendamiento_path' => $cotizacion->solicitud_arrendamiento_path,
            'verificacion_habilitacion_path' => $cotizacion->verificacion_habilitacion_path,
            'cotizacion' => $cotizacion->fresh(['user', 'evento', 'clientes', 'servicios', 'tarifas.evento', 'tarifas.espacio'])
        ]);
    }
}

