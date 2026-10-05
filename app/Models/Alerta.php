<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alerta extends Model
{
    protected $table = 'alertas';

    protected $fillable = [
        'planta_vendida_id',
        'tipo',
        'variable',
        'valor_medido',
        'limite',
        'rango_minimo',
        'rango_maximo',
        'direccion',
        'mensaje',
        'leida',
        'resuelta_en',
    ];

    protected function casts(): array
    {
        return [
            'valor_medido' => 'decimal:2',
            'limite' => 'decimal:2',
            'rango_minimo' => 'decimal:2',
            'rango_maximo' => 'decimal:2',
            'leida' => 'boolean',
            'resuelta_en' => 'immutable_datetime',
        ];
    }

    public function plantaVendida(): BelongsTo
    {
        return $this->belongsTo(PlantaVendida::class, 'planta_vendida_id');
    }
}
