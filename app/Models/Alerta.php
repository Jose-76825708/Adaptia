<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use InvalidArgumentException;

class Alerta extends Model
{
    protected $table = 'alertas';

    protected $fillable = [
        'planta_id',
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

    protected static function booted(): void
    {
        static::saving(function (self $alerta) {
            $fkActivas = 0;
            $fkActivas += (int) ! is_null($alerta->planta_id);
            $fkActivas += (int) ! is_null($alerta->planta_vendida_id);

            if ($fkActivas !== 1) {
                throw new InvalidArgumentException('Una alerta debe tener exactamente una FK activa: planta_id o planta_vendida_id.');
            }
        });
    }

    public function planta(): BelongsTo
    {
        return $this->belongsTo(Planta::class, 'planta_id');
    }

    public function plantaVendida(): BelongsTo
    {
        return $this->belongsTo(PlantaVendida::class, 'planta_vendida_id');
    }
}
