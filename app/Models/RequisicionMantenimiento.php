<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequisicionMantenimiento extends Model
{
    use HasFactory;

    protected $table = 'requisiciones_mantenimiento';

    protected $fillable = ['caso_id', 'autor_id', 'estado', 'motivo'];

    public function caso(): BelongsTo
    {
        return $this->belongsTo(CasoMantenimiento::class, 'caso_id');
    }

    public function autor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'autor_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(RequisicionItem::class, 'requisicion_id');
    }
}
