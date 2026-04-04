<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Model: Medição de execução de obra.
 *
 * Cada registro representa uma medição oficial do avanço financeiro
 * de um contrato. Saldo e percentual são calculados automaticamente
 * pelo ExecucaoObraController no momento do registro.
 */
class ExecucaoObra extends Model
{
    protected $table = 'execucao_obras';

    protected $fillable = [
        'contrato_id',
        'data_medicao',
        'valor_medido',
        'saldo_contratual',
        'percentual_executado',
        'observacao',
    ];

    protected $casts = [
        'data_medicao'         => 'date',
        'valor_medido'         => 'decimal:2',
        'saldo_contratual'     => 'decimal:2',
        'percentual_executado' => 'decimal:2',
    ];

    // ── Constantes de papel ───────────────────────────────────────

    const PAPEL_ENGENHEIRO = 'engenheiro';
    const PAPEL_FISCAL     = 'fiscal';
    const PAPEL_SUPERVISOR = 'supervisor';

    public static array $papeis = [
        self::PAPEL_ENGENHEIRO => 'Engenheiro',
        self::PAPEL_FISCAL     => 'Fiscal',
        self::PAPEL_SUPERVISOR => 'Supervisor',
    ];

    // ── Relacionamentos ───────────────────────────────────────────

    public function contrato(): BelongsTo
    {
        return $this->belongsTo(Contrato::class);
    }

    /**
     * Responsáveis pela medição (N:M com papel).
     * Pivot: execucao_responsaveis { execucao_obra_id, user_id, papel }
     */
    public function responsaveis(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'execucao_responsaveis',
            'execucao_obra_id',
            'user_id'
        )
            ->withPivot('papel')
            ->withTimestamps()
            ->orderBy('execucao_responsaveis.papel');
    }

    /**
     * Documentos anexados a esta medição (boletins, fotos, planilhas).
     * Reutiliza a tabela polimórfica `documentos`.
     */
    public function documentos(): MorphMany
    {
        return $this->morphMany(Documento::class, 'documentable');
    }
}
