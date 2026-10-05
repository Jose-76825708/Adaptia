<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LecturaSensor extends Model
{
    protected $table = 'lecturas_sensores';

    protected $fillable = [
        'planta_vendida_id',
        'humedad_suelo',
        'temperatura',
        'humedad_ambiental',
        'luz',
        'fecha_hora',
    ];

    protected function casts(): array
    {
        return [
            'fecha_hora' => 'immutable_datetime',
        ];
    }

    public function plantaVendida(): BelongsTo
    {
        return $this->belongsTo(PlantaVendida::class, 'planta_vendida_id');
    }
}
