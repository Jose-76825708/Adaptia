<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecursoCatalogo extends Model
{
    use HasFactory;

    protected $table = 'recursos_catalogo';

    protected $fillable = ['tipo', 'codigo', 'nombre', 'unidad', 'stock_actual', 'stock_minimo'];

    protected function casts(): array
    {
        return ['stock_actual' => 'decimal:2', 'stock_minimo' => 'decimal:2'];
    }
}
