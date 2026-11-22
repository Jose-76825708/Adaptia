<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoGestionInventario extends Model
{
    use HasFactory;

    protected $table = 'movimientos_gestion_inventario';

    protected $fillable = [
        'gestion_id', 'tipo', 'planta_id', 'recurso_id', 'movimiento_inventario_id',
        'cantidad', 'stock_antes', 'stock_despues', 'motivo', 'autor_id',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'decimal:2',
            'stock_antes' => 'decimal:2',
            'stock_despues' => 'decimal:2',
        ];
    }

    public function gestion(): BelongsTo
    {
        return $this->belongsTo(GestionInventario::class, 'gestion_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }
}
