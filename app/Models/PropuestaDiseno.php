<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PropuestaDiseno extends Model
{
    use HasFactory;

    protected $table='propuestas_diseno';

    protected $fillable=['proyecto_id','autor_id','version','concepto','costo_plantas','costo_mano_obra','total','decision','motivo_decision','decidida_en'];

    protected function casts(): array
    {
        return ['costo_plantas'=>'decimal:2','costo_mano_obra'=>'decimal:2','total'=>'decimal:2','decidida_en'=>'immutable_datetime'];
    }

    public function proyecto(): BelongsTo
    {
        return $this->belongsTo(ProyectoPaisajismo::class,'proyecto_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class,'autor_id');
    }

    public function plantas(): HasMany
    {
        return $this->hasMany(PropuestaPlanta::class,'propuesta_id');
    }
}
