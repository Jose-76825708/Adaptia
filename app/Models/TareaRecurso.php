<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TareaRecurso extends Model
{
    use HasFactory;

    protected $table = 'tarea_recursos';

    protected $fillable = ['tarea_id', 'recurso_id', 'cantidad'];

    protected function casts(): array
    {
        return ['cantidad' => 'decimal:2'];
    }

    public function tarea(): BelongsTo
    {
        return $this->belongsTo(TareaPlan::class, 'tarea_id');
    }

    public function recurso(): BelongsTo
    {
        return $this->belongsTo(RecursoCatalogo::class, 'recurso_id');
    }
}
