<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventoEjecucion extends Model
{
    use HasFactory;

    protected $table = 'eventos_ejecucion';

    protected $fillable = ['caso_id', 'tarea_id', 'tipo', 'descripcion', 'autor_id', 'fecha', 'resuelta_en'];

    protected function casts(): array
    {
        return ['fecha' => 'immutable_datetime', 'resuelta_en' => 'immutable_datetime'];
    }

    public function caso(): BelongsTo
    {
        return $this->belongsTo(CasoMantenimiento::class, 'caso_id');
    }

    public function tarea(): BelongsTo
    {
        return $this->belongsTo(TareaPlan::class, 'tarea_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }
}
