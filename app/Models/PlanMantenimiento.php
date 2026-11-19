<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlanMantenimiento extends Model
{
    use HasFactory;

    protected $table = 'planes_mantenimiento';

    protected $fillable = ['caso_id', 'autor_id', 'version', 'generado_en', 'descripcion'];

    public function caso(): BelongsTo
    {
        return $this->belongsTo(CasoMantenimiento::class, 'caso_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }

    public function tareas(): HasMany
    {
        return $this->hasMany(TareaPlan::class, 'plan_id');
    }
}
