<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class DetalleRecepcion extends Model
{
    protected $table = 'detalle_recepciones';

    protected $fillable = [
        'recepcion_id',
        'planta_id',
        'cantidad_esperada',
        'cantidad_recibida',
        'unidad',
        'estado_item',
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
