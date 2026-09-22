<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Planta extends Model
{
    use HasFactory;
    protected $fillable = ['tipo_planta_id','nombre','descripcion','imagen','luz_requerida','frecuencia_riego','tamaño_adulto','nivel_cuidado','tipo_ambiente','toxicidad','estetica','precio','stock_actual','stock_minimo'];

    public function tipoPlanta(): BelongsTo
    {
        return $this->belongsTo(TipoPlanta::class, 'tipo_planta_id');
    }
}
