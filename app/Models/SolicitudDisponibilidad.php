<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitudDisponibilidad extends Model
{
    use HasFactory;

    protected $table = 'solicitudes_disponibilidad';

    protected $fillable = [
        'producto_campana_id',
        'faltante',
        'estado',
        'notas',
        'gestionada_por_id',
        'gestionada_en',
    ];

    protected function casts(): array
    {
        return [
            'faltante' => 'integer',
            'gestionada_en' => 'datetime',
        ];
    }

    public function productoCampana(): BelongsTo
    {
        return $this->belongsTo(ProductoCampana::class, 'producto_campana_id');
    }

    public function gestionadaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gestionada_por_id');
    }
}
