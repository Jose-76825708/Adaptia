<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecepcionInventario extends Model
{
    use HasFactory;

    protected $table = 'recepciones_inventario';

    protected $fillable = [
        'gestion_id', 'cantidad_recibida', 'observaciones', 'conforme', 'cantidad_aceptada',
        'autor_id', 'verificada_por_id', 'verificada_en',
    ];

    protected function casts(): array
    {
        return [
            'cantidad_recibida' => 'decimal:2',
            'cantidad_aceptada' => 'decimal:2',
            'conforme' => 'boolean',
            'verificada_en' => 'datetime',
        ];
    }

    public function gestion(): BelongsTo
    {
        return $this->belongsTo(GestionInventario::class, 'gestion_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }
}
