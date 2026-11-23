<?php

namespace App\Models;

use Database\Factories\EvidenciaCasoFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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
        'public_id',
        'ruta_local',
        'descripcion',
    ];

    public function getFotoUrlAttribute(): ?string
    {
        if (!$this->ruta) {
            return null;
        }

        if ($this->public_id || filter_var($this->ruta, FILTER_VALIDATE_URL)) {
            return $this->ruta;
        }

        return Storage::disk('public')->url($this->ruta);
    }

    public function caso(): BelongsTo
    {
        return $this->belongsTo(CasoMantenimiento::class, 'caso_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }
}
