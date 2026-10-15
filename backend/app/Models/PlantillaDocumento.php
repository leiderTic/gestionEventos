<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlantillaDocumento extends Model
{
    use HasFactory;

    protected $table = 'plantillas_documentos';

    protected $fillable = [
        'codigo',
        'titulo',
        'prefijo_cite',
        'parametros_default',
        'contenido_estatico',
        'activo',
    ];

    protected $casts = [
        'parametros_default' => 'array',
        'contenido_estatico' => 'array',
        'activo' => 'boolean',
    ];

    public function documentos(): HasMany
    {
        return $this->hasMany(CotizacionDocumento::class, 'plantilla_id');
    }
}
