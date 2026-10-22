<?php

namespace App\Http\Controllers;

use App\Models\Espacio;
use App\Models\Tarifa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EspacioController extends Controller
{
    public function index()
    {
        $espacios = Espacio::with(['tipoEspacio', 'bloque', 'tarifas.temporada', 'tarifas.evento'])
            ->orderBy('id', 'asc')
            ->get();
        return response()->json($espacios);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'dias_max' => 'nullable|integer|min:1',
            'area_m2' => 'required|numeric|min:0',
            'aforo' => 'required|integer|min:0',
            'tipo_espacio_id' => 'required|exists:tipo_espacios,id',
            'bloque_id' => 'required|exists:bloques,id',
            'tarifas' => 'nullable|array',
            'tarifas.*.evento_id' => 'required|exists:eventos,id',
            'tarifas.*.temporada_id' => 'required|exists:temporadas,id',
            'tarifas.*.precio_dia' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request) {
            $espacio = Espacio::create([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'dias_max' => $request->dias_max,
                'area_m2' => $request->area_m2,
                'aforo' => $request->aforo,
                'tipo_espacio_id' => $request->tipo_espacio_id,
                'bloque_id' => $request->bloque_id,
            ]);

            if ($request->filled('tarifas')) {
                $this->sincronizarTarifas($espacio, $request->tarifas);
            }

            return response()->json(
                $espacio->load(['tipoEspacio', 'bloque', 'tarifas.temporada', 'tarifas.evento']),
                201
            );
        });
    }

    public function show(string $id)
    {
        $espacio = Espacio::with(['tipoEspacio', 'bloque', 'tarifas.temporada', 'tarifas.evento'])->findOrFail($id);
        return response()->json($espacio);
    }

    public function update(Request $request, string $id)
    {
        $espacio = Espacio::findOrFail($id);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'dias_max' => 'nullable|integer|min:1',
            'area_m2' => 'required|numeric|min:0',
            'aforo' => 'required|integer|min:0',
            'tipo_espacio_id' => 'required|exists:tipo_espacios,id',
            'bloque_id' => 'required|exists:bloques,id',
            'tarifas' => 'nullable|array',
            'tarifas.*.evento_id' => 'required|exists:eventos,id',
            'tarifas.*.temporada_id' => 'required|exists:temporadas,id',
            'tarifas.*.precio_dia' => 'required|numeric|min:0',
        ]);

        return DB::transaction(function () use ($request, $espacio) {
            $espacio->update([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'dias_max' => $request->dias_max,
                'area_m2' => $request->area_m2,
                'aforo' => $request->aforo,
                'tipo_espacio_id' => $request->tipo_espacio_id,
                'bloque_id' => $request->bloque_id,
            ]);

            if ($request->has('tarifas') && is_array($request->tarifas)) {
                $this->sincronizarTarifas($espacio, $request->tarifas);
            }

            return response()->json(
                $espacio->load(['tipoEspacio', 'bloque', 'tarifas.temporada', 'tarifas.evento'])
            );
        });
    }

    public function destroy(string $id)
    {
        $espacio = Espacio::findOrFail($id);
        $espacio->delete();

        return response()->json(['message' => 'Espacio eliminado correctamente']);
    }

    /**
     * Crea o actualiza la matriz de tarifas (Temporada Baja / Alta por Tipo de Evento) para un espacio.
     */
    private function sincronizarTarifas(Espacio $espacio, array $tarifas): void
    {
        foreach ($tarifas as $item) {
            Tarifa::updateOrCreate(
                [
                    'espacio_id' => $espacio->id,
                    'evento_id' => $item['evento_id'],
                    'temporada_id' => $item['temporada_id'],
                ],
                [
                    'precio_dia' => $item['precio_dia'],
                ]
            );
        }
    }
}
