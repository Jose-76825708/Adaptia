<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    protected $casts = [
        'tipo' => 'string',
        'cantidad' => 'integer',
        'stock_antes' => 'integer',
        'stock_despues' => 'integer',
    ];

    public function planta(): BelongsTo
    {
        return $this->belongsTo(Planta::class, 'planta_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
