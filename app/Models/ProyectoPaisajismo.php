<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProyectoPaisajismo extends Model
{
    use HasFactory;

    protected $table='proyectos_paisajismo';

    protected $fillable=[
        'codigo','solicitante_id','nombre','ubicacion','descripcion','area_m2','presupuesto_estimado','fecha_deseada','estado',
    ];

    protected function casts(): array
    {
        return ['area_m2'=>'decimal:2','presupuesto_estimado'=>'decimal:2','fecha_deseada'=>'date'];
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(User::class,'solicitante_id');
    }

    public function propuestas(): HasMany
    {
        return $this->hasMany(PropuestaDiseno::class,'proyecto_id');
    }
}
