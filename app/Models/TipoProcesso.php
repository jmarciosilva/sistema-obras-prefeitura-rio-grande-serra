<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: Tipo de processo administrativo (Alvará de Construção, Habite-se, SISOBRA...).
 *
 * @property int    $id
 * @property string $nome
 * @property string|null $sigla
 * @property int    $ordem
 */
class TipoProcesso extends Model
{
    protected $table = 'tipos_processo';

    protected $fillable = [
        'nome',
        'sigla',
        'ordem',
    ];

    // ── Relacionamentos ───────────────────────────────────────────

    public function processos(): HasMany
    {
        return $this->hasMany(Processo::class);
    }

    // ── Scopes ───────────────────────────────────────────────────

    public function scopeOrdenados($query)
    {
        return $query->orderBy('ordem')->orderBy('nome');
    }
}
