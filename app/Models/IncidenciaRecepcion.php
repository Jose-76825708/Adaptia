<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidenciaRecepcion extends Model
{
    protected $table = 'incidencias_recepcion';

    protected $fillable = [
        'recepcion_id',
        'planta_id',
        'cantidad_esperada',
        'cantidad_recibida',
        'diferencia',
        'motivo',
        'estado',
        'observaciones',
    ];

    public function recepcion(): BelongsTo
    {
        return $this->belongsTo(Recepcion::class, 'recepcion_id');
    }

    public function planta(): BelongsTo
    {
        return $this->belongsTo(Planta::class, 'planta_id');
    }
}
