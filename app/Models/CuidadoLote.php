<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CuidadoLote extends Model
{
    protected $table = 'cuidados_lote';

    protected $fillable = ['lote_id', 'user_id', 'tipo', 'fecha', 'observacion'];

    protected function casts(): array
    {
        return ['fecha' => 'date'];
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(LoteProduccion::class, 'lote_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
