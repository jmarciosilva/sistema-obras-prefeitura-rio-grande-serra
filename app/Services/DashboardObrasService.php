<?php

namespace App\Services;

use App\Http\Resources\Api\V1\ContratoResource;
use App\Models\Contrato;
use App\Models\ExecucaoObra;
use App\Models\Obra;
use App\Models\StatusObra;
use Illuminate\Support\Collection;

/**
 * Indicadores de Obras para a API mobile (Fase 8).
 *
 * Usa EXATAMENTE as mesmas regras do DashboardController web
 * (filtros por nome de status, somas globais, janelas de 30/60 dias).
 * Se uma regra mudar lá, mude aqui também.
 *
 * Diferença intencional: os alertas aqui são CONTAGENS totais, enquanto o
 * dashboard web lista no máximo 10 (contratos) / 8 (obras) itens.
 */
class DashboardObrasService
{
    public const DIAS_CONTRATO_VENCENDO = 30;
    public const DIAS_SEM_MEDICAO       = 60;

    public function resumoObras(): array
    {
        return [
            'total'       => Obra::count(),
            'em_execucao' => Obra::whereHas('status', fn($q) => $q->where('nome', 'like', '%Execu%'))->count(),
            'concluidas'  => Obra::whereHas('status', fn($q) => $q->where('nome', 'like', '%Conclu%'))->count(),
            ...$this->financeiroGeral(),
        ];
    }

    /**
     * Visão executiva de contratos.
     *
     * Situações: mesma regra da feature Contratos (ContratoResource::situacaoVigencia()),
     * então a soma das quatro situações é sempre igual ao total.
     * Financeiro: mesmas somas globais de resumoObras() — todas as medições
     * pertencem a um contrato (contrato_id obrigatório), logo os totais coincidem.
     */
    public function resumoContratos(): array
    {
        $porSituacao = Contrato::query()
            ->get(['id', 'vigencia_contrato'])
            ->countBy(fn(Contrato $c) => ContratoResource::situacaoVigencia($c));

        return [
            'total'          => $porSituacao->sum(),
            'vigentes'       => $porSituacao->get(ContratoResource::SITUACAO_VIGENTE, 0),
            'vence_em_breve' => $porSituacao->get(ContratoResource::SITUACAO_VENCE_EM_BREVE, 0),
            'vencidos'       => $porSituacao->get(ContratoResource::SITUACAO_VENCIDO, 0),
            'sem_vigencia'   => $porSituacao->get(ContratoResource::SITUACAO_SEM_VIGENCIA, 0),
            ...$this->financeiroGeral(),
        ];
    }

    /**
     * Somas globais (todas as medições), mesma regra do dashboard web:
     * saldo nunca negativo; percentual limitado a 100.
     */
    private function financeiroGeral(): array
    {
        $valorContratado = (float) Contrato::sum('valor_contrato');
        $valorMedido     = (float) ExecucaoObra::sum('valor_medido');

        return [
            'valor_contratado'     => round($valorContratado, 2),
            'valor_medido'         => round($valorMedido, 2),
            'saldo'                => round(max($valorContratado - $valorMedido, 0), 2),
            'percentual_executado' => $valorContratado > 0
                ? round(min(($valorMedido / $valorContratado) * 100, 100), 2)
                : 0.0,
        ];
    }

    public function obrasPorStatus(): Collection
    {
        return StatusObra::withCount('obras')
            ->orderBy('ordem')
            ->get()
            ->map(fn(StatusObra $s) => [
                'id'    => $s->id,
                'nome'  => $s->nome,
                'cor'   => $s->cor,
                'total' => $s->obras_count,
            ]);
    }

    public function alertas(): array
    {
        return [
            'contratos_vencidos' => Contrato::whereNotNull('vigencia_contrato')
                ->where('vigencia_contrato', '<', now())
                ->count(),

            'contratos_vencendo' => Contrato::whereNotNull('vigencia_contrato')
                ->whereBetween('vigencia_contrato', [now(), now()->addDays(self::DIAS_CONTRATO_VENCENDO)])
                ->count(),

            // Obras em execução, com contrato, sem medição nos últimos 60 dias
            'obras_sem_medicao' => Obra::whereHas('status', fn($q) => $q->where('nome', 'like', '%Execu%'))
                ->whereDoesntHave('contratos.execucoes', function ($q) {
                    $q->where('data_medicao', '>=', now()->subDays(self::DIAS_SEM_MEDICAO));
                })
                ->whereHas('contratos')
                ->count(),
        ];
    }
}
