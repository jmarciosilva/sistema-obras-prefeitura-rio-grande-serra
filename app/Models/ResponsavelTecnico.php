<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: Responsável técnico (engenheiro/arquiteto) vinculado a processos administrativos.
 *
 * @property int    $id
 * @property string $nome
 * @property string|null $registro  — CREA/CAU
 * @property string|null $telefone
 */
class ResponsavelTecnico extends Model
{
    // O plural "regular" do Eloquent geraria "responsavel_tecnicos" — a tabela usa o
    // plural correto em português ("responsaveis_tecnicos"), por isso precisa ser explícito.
    protected $table = 'responsaveis_tecnicos';

    protected $fillable = [
        'nome',
        'registro',
        'telefone',
    ];

    // ── Relacionamentos ───────────────────────────────────────────

    public function processos(): HasMany
    {
        return $this->hasMany(Processo::class);
    }

    // ── Scopes ───────────────────────────────────────────────────

    public function scopeOrdenados($query)
    {
        return $query->orderBy('nome');
    }
}
