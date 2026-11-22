<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GestionInventario extends Model
{
    use HasFactory;

    protected $table = 'gestiones_inventario';

    protected $fillable = [
        'codigo', 'tipo_item', 'planta_id', 'recurso_id', 'cantidad_requerida', 'motivo',
        'stock_sistema', 'estado', 'creador_id',
        'cantidad_fisica', 'cantidad_buen_estado', 'cantidad_disponible', 'faltante', 'observaciones_verificacion',
        'cantidad_reposicion', 'proveedor', 'costo_estimado', 'numero_orden', 'fecha_compra', 'costo_total',
        'cantidad_ingresada', 'documento_ingreso', 'ubicacion', 'resultado', 'cerrada_en',
    ];

    protected function casts(): array
    {
        return [
            'cantidad_requerida' => 'decimal:2',
            'stock_sistema' => 'decimal:2',
            'cantidad_fisica' => 'decimal:2',
            'cantidad_buen_estado' => 'decimal:2',
            'cantidad_disponible' => 'decimal:2',
            'faltante' => 'decimal:2',
            'cantidad_reposicion' => 'decimal:2',
            'costo_estimado' => 'decimal:2',
            'fecha_compra' => 'date',
            'costo_total' => 'decimal:2',
            'cantidad_ingresada' => 'decimal:2',
            'cerrada_en' => 'datetime',
        ];
    }

    public function planta(): BelongsTo
    {
        return $this->belongsTo(Planta::class, 'planta_id');
    }

    public function recurso(): BelongsTo
    {
        return $this->belongsTo(RecursoCatalogo::class, 'recurso_id');
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creador_id');
    }

    public function recepciones(): HasMany
    {
        return $this->hasMany(RecepcionInventario::class, 'gestion_id');
    }

    public function incidencias(): HasMany
    {
        return $this->hasMany(IncidenciaInventario::class, 'gestion_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoGestionInventario::class, 'gestion_id');
    }

    public function nombreItem(): string
    {
        return $this->tipo_item === 'planta'
            ? (string) $this->planta?->nombre
            : (string) $this->recurso?->nombre;
    }

    public function unidad(): string
    {
        return $this->tipo_item === 'planta' ? 'unidad' : (string) ($this->recurso?->unidad ?? 'unidad');
    }
}
