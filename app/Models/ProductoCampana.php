<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductoCampana extends Model
{
    use HasFactory;

    protected $table = 'productos_campana';

    protected $fillable = [
        'campana_id',
        'planta_id',
        'cantidad_meta',
        'descuento_porcentaje',
        'incluida',
        'cantidad_vendida_evaluada',
        'porcentaje_cumplimiento',
        'ingresos_estimados',
    ];

    protected function casts(): array
    {
        return [
            'cantidad_meta' => 'integer',
            'descuento_porcentaje' => 'decimal:2',
            'incluida' => 'boolean',
            'cantidad_vendida_evaluada' => 'integer',
            'porcentaje_cumplimiento' => 'decimal:2',
            'ingresos_estimados' => 'decimal:2',
        ];
    }

    public function campana(): BelongsTo
    {
        return $this->belongsTo(CampanaComercial::class, 'campana_id');
    }

    public function planta(): BelongsTo
    {
        return $this->belongsTo(Planta::class, 'planta_id');
    }

    public function solicitudDisponibilidad(): HasOne
    {
        return $this->hasOne(SolicitudDisponibilidad::class, 'producto_campana_id');
    }
}
