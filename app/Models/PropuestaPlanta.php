<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropuestaPlanta extends Model
{
    use HasFactory;

    protected $table='propuesta_plantas';

    protected $fillable=['propuesta_id','planta_id','cantidad','precio_unitario'];

    protected function casts(): array
    {
        return ['cantidad'=>'integer','precio_unitario'=>'decimal:2'];
    }

    public function propuesta(): BelongsTo
    {
        return $this->belongsTo(PropuestaDiseno::class,'propuesta_id');
    }

    public function planta(): BelongsTo
    {
        return $this->belongsTo(Planta::class,'planta_id');
    }
}
