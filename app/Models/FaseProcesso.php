<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: Fase de tramitação de um processo administrativo.
 *
 * ⚠️  Vocabulário ainda não definitivo — validar com as técnicas do Departamento
 * de Obras Particulares (ver roadmap-modulo-processos-administrativos.md, seção 5).
 * Por isso é uma tabela de domínio editável via este model, não um enum de banco.
 *
 * @property int    $id
 * @property string $nome
 * @property string $cor    — hex, ex: #0d6efd
 * @property int    $ordem
 */
class FaseProcesso extends Model
{
    protected $table = 'fases_processo';

    protected $fillable = [
        'nome',
        'cor',
        'ordem',
    ];

    // ── Relacionamentos ───────────────────────────────────────────

    public function processos(): HasMany
    {
        return $this->hasMany(Processo::class, 'fase_atual_id');
    }

    public function tramites(): HasMany
    {
        return $this->hasMany(Tramite::class, 'fase_id');
    }

    // ── Scopes ───────────────────────────────────────────────────

    public function scopeOrdenados($query)
    {
        return $query->orderBy('ordem')->orderBy('nome');
    }
}
