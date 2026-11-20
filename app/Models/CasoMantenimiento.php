<?php

namespace App\Models;

use Database\Factories\CasoMantenimientoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function evidencias(): HasMany
    {
        return $this->hasMany(EvidenciaCaso::class, 'caso_id');
    }

    public function diagnosticos(): HasMany
    {
        return $this->hasMany(DiagnosticoCaso::class, 'caso_id');
    }

    public function planes(): HasMany
    {
        return $this->hasMany(PlanMantenimiento::class, 'caso_id');
    }

    public function informes(): HasMany
    {
        return $this->hasMany(InformeCierre::class, 'caso_id');
    }
}
