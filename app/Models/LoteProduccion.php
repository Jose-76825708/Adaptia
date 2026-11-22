<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LoteProduccion extends Model
{
    protected $table = 'lotes_produccion';

    protected $fillable = [
        'codigo',
        'responsable_id',
        'planta_id',
        'motivo',
        'cantidad_meta',
        'observaciones',
        'estado',
        'metodo_propagacion',
        'cantidad_material',
        'sustrato_mezcla',
        'fecha_propagacion',
        'cantidad_sembrada',
        'cantidad_perdida',
        'cantidad_categoria_a',
        'cantidad_categoria_b',
        'cantidad_descarte',
        'cantidad_producida',
        'movimiento_inventario_id',
        'cerrado_en',
    ];

    protected function casts(): array
    {
        return [
            'fecha_propagacion' => 'date',
            'cerrado_en' => 'datetime',
        ];
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsable_id');
    }

    public function planta(): BelongsTo
    {
        return $this->belongsTo(Planta::class, 'planta_id');
    }

    public function cuidados(): HasMany
    {
        return $this->hasMany(CuidadoLote::class, 'lote_id');
    }

    public function revisiones(): HasMany
    {
        return $this->hasMany(RevisionLote::class, 'lote_id');
    }

    public function movimiento(): BelongsTo
    {
        return $this->belongsTo(MovimientoInventario::class, 'movimiento_inventario_id');
    }
}
