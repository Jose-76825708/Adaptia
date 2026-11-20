<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemChecklist extends Model
{
    use HasFactory;

    protected $table = 'items_checklist';

    protected $fillable = ['tarea_id', 'descripcion', 'cumplido'];

    protected function casts(): array
    {
        return ['cumplido' => 'boolean'];
    }

    public function tarea(): BelongsTo
    {
        return $this->belongsTo(TareaPlan::class, 'tarea_id');
    }
}
