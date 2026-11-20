<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequisicionItem extends Model
{
    use HasFactory;

    protected $table = 'requisicion_items';

    protected $fillable = ['requisicion_id', 'recurso_id', 'cantidad_solicitada', 'cantidad_recibida', 'estado'];

    protected function casts(): array
    {
        return ['cantidad_solicitada' => 'decimal:2', 'cantidad_recibida' => 'decimal:2'];
    }

    public function requisicion(): BelongsTo
    {
        return $this->belongsTo(RequisicionMantenimiento::class, 'requisicion_id');
    }

    public function recurso(): BelongsTo
    {
        return $this->belongsTo(RecursoCatalogo::class, 'recurso_id');
    }
}
