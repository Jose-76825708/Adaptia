<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AvanceInstalacion extends Model
{
    use HasFactory;

    protected $table='avances_instalacion';

    protected $fillable=['proyecto_id','autor_id','descripcion','porcentaje'];

    protected function casts(): array
    {
        return ['porcentaje'=>'integer'];
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
