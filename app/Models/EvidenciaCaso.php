<?php

namespace App\Models;

use Database\Factories\EvidenciaCasoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvidenciaCaso extends Model
{
    /** @use HasFactory<EvidenciaCasoFactory> */
    use HasFactory;

    protected $table = 'evidencias_caso';

    protected $fillable = [
        'caso_id',
        'autor_id',
        'tipo',
        'contenido',
        'ruta',
        'descripcion',
    ];

    public function caso(): BelongsTo
    {
        return $this->belongsTo(CasoMantenimiento::class, 'caso_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }
}
