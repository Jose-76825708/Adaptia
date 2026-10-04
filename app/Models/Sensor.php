<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sensor extends Model
{
    use HasFactory;

    protected $table = 'sensores';

    protected $fillable = ['identificador_fisico', 'estado'];

    public function plantaVendida(): HasOne
    {
        return $this->hasOne(PlantaVendida::class, 'sensor_id');
    }
}