<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoMaterial extends Model
{
    use HasFactory;

    protected $table = 'movimientos_material';

    protected $fillable = ['requisicion_item_id', 'caso_id', 'recurso_id', 'tipo', 'stock_antes', 'stock_despues', 'cantidad', 'motivo', 'autor_id'];

    protected function casts(): array
    {
        return ['stock_antes' => 'decimal:2', 'stock_despues' => 'decimal:2', 'cantidad' => 'decimal:2'];
    }

    public function requisicionItem(): BelongsTo
    {
        return $this->belongsTo(RequisicionItem::class, 'requisicion_item_id');
    }
}
