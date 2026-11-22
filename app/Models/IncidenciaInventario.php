<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidenciaInventario extends Model
{
    use HasFactory;

    protected $table = 'incidencias_inventario';

    protected $fillable = [
        'gestion_id', 'recepcion_id', 'tipo', 'cantidad_afectada', 'descripcion', 'estado',
        'resolucion', 'autor_id', 'gestionada_por_id', 'gestionada_en',
    ];

    protected function casts(): array
    {
        return [
            'cantidad_afectada' => 'decimal:2',
            'gestionada_en' => 'datetime',
        ];
    }

    public function gestion(): BelongsTo
    {
        return $this->belongsTo(GestionInventario::class, 'gestion_id');
    }

    public function recepcion(): BelongsTo
    {
        return $this->belongsTo(RecepcionInventario::class, 'recepcion_id');
    }
}
