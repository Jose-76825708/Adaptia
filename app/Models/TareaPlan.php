<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TareaPlan extends Model
{
    use HasFactory;

    protected $table = 'tareas_plan';

    protected $fillable = ['plan_id', 'codigo', 'titulo', 'descripcion', 'prioridad', 'criticidad', 'depende_de_tarea_id', 'fecha_estimada', 'duracion_dias', 'estado'];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanMantenimiento::class, 'plan_id');
    }

    public function predecesora(): BelongsTo
    {
        return $this->belongsTo(TareaPlan::class, 'depende_de_tarea_id');
    }

    public function recursos(): HasMany
    {
        return $this->hasMany(TareaRecurso::class, 'tarea_id');
    }

    public function checklist(): HasMany
    {
        return $this->hasMany(ItemChecklist::class, 'tarea_id');
    }
}
