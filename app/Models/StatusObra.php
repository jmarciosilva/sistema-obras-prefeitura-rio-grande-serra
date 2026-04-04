<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: Status das obras.
 *
 * @property int    $id
 * @property string $nome
 * @property string $cor    — hex, ex: #28a745
 * @property int    $ordem
 */
class StatusObra extends Model
{
    protected $fillable = [
        'nome',
        'cor',
        'ordem',
    ];

    // ── Relacionamentos ───────────────────────────────────────────

    public function obras(): HasMany
    {
        return $this->hasMany(Obra::class, 'status_obra_id');
    }

    // ── Scopes ───────────────────────────────────────────────────

    /**
     * Ordenados pela coluna `ordem` — usado em selects e listagens.
     * Chamado como: StatusObra::ordenados()->get()
     */
    public function scopeOrdenados($query)
    {
        return $query->orderBy('ordem');
    }
}
