<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Contrato;
use App\Models\ExecucaoObra;
use Illuminate\Http\Request;

/**
 * Visão executiva de uma obra. Espera status, contratos.empresa e
 * contratos.execucoes carregados.
 */
class ObraDetalheResource extends ObraResource
{
    /** Quantidade de medições retornadas em "ultimas_medicoes". */
    private const LIMITE_MEDICOES = 5;

    public function toArray(Request $request): array
    {
        $contratos = $this->contratos;

        $ultimasMedicoes = $contratos
            ->flatMap(fn(Contrato $c) => $c->execucoes->map(fn(ExecucaoObra $e) => [
                'id'                   => $e->id,
                'contrato_id'          => $c->id,
                'numero_contrato'      => $c->numero_contrato_ano,
                'data_medicao'         => $e->data_medicao?->toDateString(),
                'valor_medido'         => (float) $e->valor_medido,
                'percentual_executado' => $e->percentual_executado !== null ? (float) $e->percentual_executado : null,
            ]))
            ->sortByDesc(fn($m) => [$m['data_medicao'], $m['id']])
            ->take(self::LIMITE_MEDICOES)
            ->values();

        return array_merge(parent::toArray($request), [
            'processo_execucao' => $this->processo_execucao,

            'contratos' => $contratos->map(fn(Contrato $c) => [
                'id'                 => $c->id,
                'numero_contrato'    => $c->numero_contrato_ano,
                'processo_licitacao' => $c->processo_licitacao,
                'empresa'            => $c->empresa ? [
                    'id'   => $c->empresa->id,
                    'nome' => $c->empresa->nomeExibicao(),
                    'cnpj' => $c->empresa->cnpj,
                ] : null,
                'data_assinatura'    => $c->data_assinatura?->toDateString(),
                'ordem_inicio'       => $c->ordem_inicio?->toDateString(),
                'vigencia_contrato'  => $c->vigencia_contrato?->toDateString(),
                'vencido'            => $c->estaVencido(),
                'valor_contrato'     => (float) $c->valor_contrato,
                'valor_medido'       => (float) $c->execucoes->sum('valor_medido'),
            ])->values(),

            'ultimas_medicoes' => $ultimasMedicoes,
        ]);
    }
}
