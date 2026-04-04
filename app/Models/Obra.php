<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * Model: Obra municipal — entidade central do sistema.
 *
 * ⚠️  IMPORTANTE — KPIs financeiros:
 * Os valores de valor_medido, saldo_contratual e percentual_executado
 * NÃO estão armazenados nesta tabela. São CALCULADOS dinamicamente
 * a partir das execuções vinculadas aos contratos.
 *
 * Para que funcionem corretamente, o relacionamento contratos.execucoes
 * deve estar carregado (eager load) antes de acessar esses accessors.
 * O ObraController@show já faz esse carregamento.
 */
class Obra extends Model
{
    protected $fillable = [
        'descricao',
        'endereco',
        'processo_execucao',
        'observacoes',
        'status_obra_id',
        'demanda_proposta_id',
    ];

    // ─────────────────────────────────────────────
    // 🔗 RELACIONAMENTOS
    // ─────────────────────────────────────────────

    public function status(): BelongsTo
    {
        return $this->belongsTo(StatusObra::class, 'status_obra_id');
    }

    public function convenios(): BelongsToMany
    {
        return $this->belongsToMany(Convenio::class, 'obra_convenio');
    }

    public function contratos(): HasMany
    {
        return $this->hasMany(Contrato::class);
    }

    public function documentos(): MorphMany
    {
        return $this->morphMany(Documento::class, 'documentable');
    }

    public function demandaProposta(): BelongsTo
    {
        return $this->belongsTo(DemandaProposta::class);
    }

    // ─────────────────────────────────────────────
    // 📊 ACCESSORS — KPIs CALCULADOS
    //
    // Dependem de contratos.execucoes estar carregado.
    // Se não estiver, fazem uma query extra automaticamente.
    // ─────────────────────────────────────────────

    /**
     * Soma de todos os valores medidos em todos os contratos da obra.
     */
    protected function valorMedido(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Se o relacionamento já foi carregado (eager load), usa a coleção em memória
                if ($this->relationLoaded('contratos')) {
                    return $this->contratos
                        ->flatMap(
                            fn($c) => $c->relationLoaded('execucoes')
                                ? $c->execucoes
                                : $c->execucoes()->get()
                        )
                        ->sum('valor_medido');
                }

                // Fallback: query direta (evitar em loops — use eager load no controller)
                return \App\Models\ExecucaoObra::whereHas(
                    'contrato',
                    fn($q) => $q->where('obra_id', $this->id)
                )->sum('valor_medido');
            }
        );
    }

    /**
     * Valor total contratado (soma de todos os contratos).
     */
    protected function valorContratado(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->relationLoaded('contratos')) {
                    return $this->contratos->sum('valor_contrato');
                }

                return $this->contratos()->sum('valor_contrato');
            }
        );
    }

    /**
     * Saldo contratual = valor_contratado - valor_medido.
     * Nunca negativo — retorna 0 quando ultrapassado.
     */
    protected function saldoContratual(): Attribute
    {
        return Attribute::make(
            get: fn() => max($this->valor_contratado - $this->valor_medido, 0)
        );
    }

    /**
     * Percentual executado = (valor_medido / valor_contratado) * 100.
     * Limitado a 100%. Retorna 0 quando não há contratos com valor.
     */
    protected function percentualExecutado(): Attribute
    {
        return Attribute::make(
            get: function () {
                $contratado = $this->valor_contratado;

                if ($contratado <= 0) {
                    return 0.0;
                }

                return min(($this->valor_medido / $contratado) * 100, 100);
            }
        );
    }
}
