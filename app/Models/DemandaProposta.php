<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: Demanda ou proposta que origina uma obra.
 *
 * Registra a solicitação ou proposta inicial (parlamentar, secretaria, etc.)
 * que depois se torna uma obra formal no sistema.
 *
 * @property int    $id
 * @property string $numero_demanda       Identificador da demanda (ex: "DEM-2024-001")
 * @property string $descricao
 * @property string $origem              (secretaria|vereador|estado|federal|outros)
 * @property string|null $solicitante
 * @property \Carbon\Carbon|null $data_solicitacao
 * @property string $situacao            (pendente|aprovada|rejeitada|em_andamento)
 * @property string|null $observacoes
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class DemandaProposta extends Model
{
    protected $table = 'demandas_propostas';

    protected $fillable = [
        'numero_demanda',
        'descricao',
        'origem',
        'solicitante',
        'data_solicitacao',
        'situacao',
        'observacoes',
    ];

    protected $casts = [
        'data_solicitacao' => 'date',
    ];

    // ── Constantes ───────────────────────────────────────────────

    const ORIGEM_SECRETARIA = 'secretaria';
    const ORIGEM_VEREADOR   = 'vereador';
    const ORIGEM_ESTADO     = 'estado';
    const ORIGEM_FEDERAL    = 'federal';
    const ORIGEM_OUTROS     = 'outros';

    public static array $origens = [
        self::ORIGEM_SECRETARIA => 'Secretaria Municipal',
        self::ORIGEM_VEREADOR   => 'Vereador / Câmara',
        self::ORIGEM_ESTADO     => 'Governo Estadual',
        self::ORIGEM_FEDERAL    => 'Governo Federal',
        self::ORIGEM_OUTROS     => 'Outros',
    ];

    const SITUACAO_PENDENTE     = 'pendente';
    const SITUACAO_APROVADA     = 'aprovada';
    const SITUACAO_REJEITADA    = 'rejeitada';
    const SITUACAO_EM_ANDAMENTO = 'em_andamento';

    public static array $situacoes = [
        self::SITUACAO_PENDENTE     => 'Pendente',
        self::SITUACAO_APROVADA     => 'Aprovada',
        self::SITUACAO_REJEITADA    => 'Rejeitada',
        self::SITUACAO_EM_ANDAMENTO => 'Em Andamento',
    ];

    // ── Relacionamentos ───────────────────────────────────────────

    public function obras(): HasMany
    {
        return $this->hasMany(Obra::class, 'demanda_proposta_id');
    }

    // ── Accessors ────────────────────────────────────────────────

    public function getOrigemLabelAttribute(): string
    {
        return self::$origens[$this->origem] ?? ucfirst($this->origem);
    }

    public function getSituacaoLabelAttribute(): string
    {
        return self::$situacoes[$this->situacao] ?? ucfirst($this->situacao);
    }

    /** Badge CSS class de acordo com a situação. */
    public function getSituacaoBadgeAttribute(): string
    {
        return match ($this->situacao) {
            'aprovada'     => 'badge-success',
            'rejeitada'    => 'badge-danger',
            'em_andamento' => 'badge-info',
            default        => 'badge-warning',
        };
    }

    // ── Scopes ───────────────────────────────────────────────────

    public function scopePendentes($query)
    {
        return $query->where('situacao', self::SITUACAO_PENDENTE);
    }

    public function scopeAprovadas($query)
    {
        return $query->where('situacao', self::SITUACAO_APROVADA);
    }
}
