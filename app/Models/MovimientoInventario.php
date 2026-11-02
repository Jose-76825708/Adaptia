<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MovimientoInventario extends Model
{
    use HasFactory;

    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'planta_id',
        'user_id',
        'tipo',
        'cantidad',
        'motivo',
        'stock_antes',
        'stock_despues',
        'movimiento_original_id',
    ];

    protected $casts = [
        'tipo' => 'string',
        'cantidad' => 'integer',
        'stock_antes' => 'integer',
        'stock_despues' => 'integer',
        'movimiento_original_id' => 'integer',
    ];

    public function planta(): BelongsTo
    {
        return $this->belongsTo(Planta::class, 'planta_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Movimiento que esta anulación compensa (solo si es un contramovimiento).
     */
    public function original(): BelongsTo
    {
        return $this->belongsTo(self::class, 'movimiento_original_id');
    }

    /**
     * Contramovimiento que anuló a este movimiento, si existe.
     */
    public function anulacion(): HasOne
    {
        return $this->hasOne(self::class, 'movimiento_original_id');
    }

    public function esAnulacion(): bool
    {
        return $this->movimiento_original_id !== null;
    }
}
