<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntregaProyecto extends Model
{
    use HasFactory;

    protected $table='entregas_proyecto';

    protected $fillable=['proyecto_id','autor_id','resumen','recomendaciones','calificacion','comentario','calificado_en'];

    protected function casts(): array
    {
        return ['calificacion'=>'integer','calificado_en'=>'immutable_datetime'];
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(ProyectoPaisajismo::class,'proyecto_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class,'autor_id');
    }
}
