<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: Órgão financiador do convênio (ex: Ministério das Cidades, SABESP...).
 *
 * @property int    $id
 * @property string $nome
 * @property string|null $sigla
 * @property string|null $esfera   (federal|estadual|municipal)
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class OrgaoFinanciador extends Model
{
    protected $table = 'orgaos_financiadores';

    protected $fillable = [
        'nome',
        'sigla',
        'esfera',
    ];

    // ── Constantes de esfera ──────────────────────────────────────

    const ESFERA_FEDERAL    = 'federal';
    const ESFERA_ESTADUAL   = 'estadual';
    const ESFERA_MUNICIPAL  = 'municipal';

    public static array $esferas = [
        self::ESFERA_FEDERAL   => 'Federal',
        self::ESFERA_ESTADUAL  => 'Estadual',
        self::ESFERA_MUNICIPAL => 'Municipal',
    ];

    // ── Relacionamentos ───────────────────────────────────────────

    public function convenios(): HasMany
    {
        return $this->hasMany(Convenio::class, 'orgao_financiador_id');
    }

    // ── Accessors ────────────────────────────────────────────────

    /** Exibe sigla entre parênteses se disponível. Ex: "Ministério das Cidades (MDS)". */
    public function getNomeComSiglaAttribute(): string
    {
        return $this->sigla
            ? "{$this->nome} ({$this->sigla})"
            : $this->nome;
    }

    public function getEsferaLabelAttribute(): string
    {
        return self::$esferas[$this->esfera] ?? ucfirst($this->esfera);
    }

    // ── Scopes ───────────────────────────────────────────────────

    public function scopeOrdenados($query)
    {
        return $query->orderBy('nome');
    }

    public function scopeEsfera($query, string $esfera)
    {
        return $query->where('esfera', $esfera);
    }
}
