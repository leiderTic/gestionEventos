<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CotizacionDocumento extends Model
{
    use HasFactory;

    protected $table = 'cotizacion_documentos';

    protected $fillable = [
        'cotizacion_id',
        'plantilla_id',
        'codigo_tipo',
        'fecha',
        'cite',
        'del',
        'plazo',
        'telefono',
        'correo_electronico',
        'hoja_ruta',
        'moscas',
        'snapshot_datos',
        'pdf_path',
        'usuario_id',
    ];

    protected $casts = [
        'fecha' => 'date',
        'snapshot_datos' => 'array',
    ];

    public function cotizacion(): BelongsTo
    {
        return $this->belongsTo(Cotizacion::class, 'cotizacion_id');
    }

    public function plantilla(): BelongsTo
    {
        return $this->belongsTo(PlantillaDocumento::class, 'plantilla_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
