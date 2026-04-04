<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Model: Contrato de licitação.
 *
 * Vincula uma obra a uma empresa contratada, definindo valor e prazos.
 * Cada contrato pode ter várias medições de execução ao longo do tempo.
 */
class Contrato extends Model
{
    protected $fillable = [
        'obra_id',
        'empresa_id',
        'processo_licitacao',
        'numero_contrato_ano',
        'data_assinatura',
        'ordem_inicio',
        'vigencia_contrato',
        'valor_contrato',
    ];

    protected $casts = [
        'data_assinatura'   => 'date',
        'ordem_inicio'      => 'date',
        'vigencia_contrato' => 'date',
        'valor_contrato'    => 'decimal:2',
    ];

    public function obra(): BelongsTo
    {
        return $this->belongsTo(Obra::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Histórico de medições/execuções deste contrato.
     * Ordenado por data para facilitar a exibição da evolução.
     */
    public function execucoes(): HasMany
    {
        return $this->hasMany(ExecucaoObra::class, 'contrato_id')
            ->orderBy('data_medicao');
    }

    /**
     * Documentos anexados a este contrato (ART, contrato assinado, aditivos).
     */
    public function documentos(): MorphMany
    {
        return $this->morphMany(Documento::class, 'documentable');
    }

    /**
     * Verifica se o contrato está com a vigência vencida.
     */
    public function estaVencido(): bool
    {
        return $this->vigencia_contrato && $this->vigencia_contrato->isPast();
    }

    /**
     * Verifica se o contrato vence nos próximos $dias dias.
     */
    public function venceEm(int $dias = 30): bool
    {
        return $this->vigencia_contrato
            && $this->vigencia_contrato->isFuture()
            && $this->vigencia_contrato->diffInDays(now()) <= $dias;
    }
}
