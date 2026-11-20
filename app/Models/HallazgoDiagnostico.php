<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HallazgoDiagnostico extends Model
{
    use HasFactory;

    protected $table = 'hallazgos_diagnostico';

    protected $fillable = [
        'diagnostico_id',
        'tipo',
        'variable',
        'severidad',
        'descripcion',
    ];

    public function diagnostico(): BelongsTo
    {
        return $this->belongsTo(DiagnosticoCaso::class, 'diagnostico_id');
    }
}
