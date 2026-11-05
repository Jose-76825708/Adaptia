<?php

namespace App\Models;

use Database\Factories\CasoMantenimientoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CasoMantenimiento extends Model
{
    /** @use HasFactory<CasoMantenimientoFactory> */
    use HasFactory;

    protected $table = 'casos_mantenimiento';

    protected $fillable = [
        'codigo',
        'solicitante_id',
        'area_verde',
        'descripcion',
        'fecha_deseada',
        'observaciones',
        'tipo_mantenimiento',
        'urgencia',
        'estado',
    ];

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }
}
