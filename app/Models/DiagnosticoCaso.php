<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiagnosticoCaso extends Model
{
    use HasFactory;

    protected $table = 'diagnosticos_caso';

    protected $fillable = [
        'caso_id',
        'autor_id',
        'version',
        'severidad',
        'resumen',
        'justificacion',
        'evaluado_en',
    ];

    public function caso(): BelongsTo
    {
        return $this->belongsTo(CasoMantenimiento::class, 'caso_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }

    public function hallazgos(): HasMany
    {
        return $this->hasMany(HallazgoDiagnostico::class, 'diagnostico_id');
    }
}
