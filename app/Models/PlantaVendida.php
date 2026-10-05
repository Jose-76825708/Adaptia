<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlantaVendida extends Model
{
    use HasFactory;

    protected $table = 'plantas_vendidas';

    protected $fillable = [
        'venta_id',
        'user_id',
        'sensor_id',
    ];

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sensor(): BelongsTo
    {
        return $this->belongsTo(Sensor::class, 'sensor_id');
    }

    public function lecturasSensores(): HasMany
    {
        return $this->hasMany(LecturaSensor::class, 'planta_vendida_id');
    }

    public function alertas(): HasMany
    {
        return $this->hasMany(Alerta::class, 'planta_vendida_id');
    }
}
