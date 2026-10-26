<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecepcionAuditoria extends Model
{
    protected $table = 'recepcion_auditorias';

    protected $fillable = [
        'recepcion_id',
        'usuario_id',
        'accion',
        'detalle',
    ];

    public function recepcion(): BelongsTo
    {
        return $this->belongsTo(Recepcion::class, 'recepcion_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
