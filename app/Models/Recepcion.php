<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Recepcion extends Model
{
    protected $table = 'recepciones';

    protected $fillable = [
        'usuario_almacen_id',
        'tipo',
        'estado',
        'fecha_recepcion',
        'observaciones',
    ];

    protected $casts = [
        'fecha_recepcion' => 'datetime',
    ];

    public function almacenista(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_almacen_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleRecepcion::class, 'recepcion_id');
    }

    public function incidencias(): HasMany
    {
        return $this->hasMany(IncidenciaRecepcion::class, 'recepcion_id');
    }

    public function auditorias(): HasMany
    {
        return $this->hasMany(RecepcionAuditoria::class, 'recepcion_id');
    }
}
