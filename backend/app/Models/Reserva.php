<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reserva extends Model
{
    use SoftDeletes;

    protected $table = 'reservas';

    protected $fillable = [
        'cotizacion_id',
        'codigo',
        'correlativo',
        'fecha_reserva',
        'estado',
        'user_id',
        'jefe_ucfchm',
        'observaciones',
    ];

    protected $casts = [
        'fecha_reserva' => 'date',
        'correlativo' => 'integer',
    ];

    public function cotizacion()
    {
        return $this->belongsTo(Cotizacion::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
