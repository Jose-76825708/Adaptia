<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InformeCierre extends Model
{
    use HasFactory;

    protected $table = 'informes_cierre';

    protected $fillable = ['caso_id', 'autor_id', 'destinatario_id', 'version', 'resumen', 'contenido', 'notificacion_pendiente', 'cerrado_en'];

    protected function casts(): array
    {
        return ['notificacion_pendiente' => 'boolean', 'cerrado_en' => 'immutable_datetime'];
    }

    public function caso(): BelongsTo
    {
        return $this->belongsTo(CasoMantenimiento::class, 'caso_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }

    public function destinatario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'destinatario_id');
    }
}
