<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CampanaComercial extends Model
{
    use HasFactory;

    protected $table = 'campanas_comerciales';

    protected $fillable = [
        'codigo',
        'objetivos',
        'presupuesto',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'creador_id',
        'ajustes_siguiente_ciclo',
        'evaluada_en',
    ];

    protected function casts(): array
    {
        return [
            'presupuesto' => 'decimal:2',
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'evaluada_en' => 'datetime',
        ];
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creador_id');
    }

    public function productos(): HasMany
    {
        return $this->hasMany(ProductoCampana::class, 'campana_id');
    }
}
