<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Planta extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipo_planta_id',
        'nombre',
        'descripcion',
        'imagen',
        'luz_requerida',
        'frecuencia_riego',
        'tamaño_adulto',
        'nivel_cuidado',
        'tipo_ambiente',
        'toxicidad',
        'estetica',
        'precio',
        'stock_actual',
        'stock_minimo',
        'humedad_suelo_min',
        'humedad_suelo_max',
        'temperatura_min',
        'temperatura_max',
        'humedad_ambiental_min',
        'humedad_ambiental_max',
        'luz_min',
        'luz_max',
    ];

    public function tipoPlanta(): BelongsTo
    {
        return $this->belongsTo(TipoPlanta::class, 'tipo_planta_id');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'planta_id');
    }
}
