<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RevisionLote extends Model
{
    protected $table = 'revisiones_lote';

    protected $fillable = ['lote_id', 'user_id', 'apta', 'plantas_perdidas', 'observaciones'];

    protected function casts(): array
    {
        return ['apta' => 'boolean'];
    }

    public function lote(): BelongsTo
    {
        return $this->belongsTo(LoteProduccion::class, 'lote_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
