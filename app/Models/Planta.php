<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Planta extends Model
{
    protected $fillable = ['tipo_planta_id','nombre','descripcion','imagen','luz_requerida','frecuencia_riego','tamaño_adulto','nivel_cuidado','tipo_ambiente','toxicidad','estetica','precio'];
}
