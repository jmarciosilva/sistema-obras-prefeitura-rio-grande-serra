<?php

namespace App\Http\Resources\Api\V1;

use App\Models\ExecucaoObra;
use Illuminate\Http\Request;

/**
 * Visão executiva de um contrato. Espera obra.status, empresa, execucoes e
 * loadSum('execucoes', 'valor_medido') carregados.
 */
class ContratoDetalheResource extends ContratoResource
{
    /** Quantidade de medições em "ultimas_medicoes" (mesmo limite do detalhe da obra). */
    private const LIMITE_MEDICOES = 5;

    public function toArray(Request $request): array
    {
        $obra = $this->obra;

        return array_merge(parent::toArray($request), [
            'obra' => $obra ? [
                'id'        => $obra->id,
                'descricao' => $obra->descricao,
                'endereco'  => $obra->endereco,
                'status'    => $obra->status ? [
                    'id'   => $obra->status->id,
                    'nome' => $obra->status->nome,
                    'cor'  => $obra->status->cor,
                ] : null,
            ] : null,

            'ultimas_medicoes' => $this->execucoes
                ->sortByDesc(fn(ExecucaoObra $e) => [$e->data_medicao?->toDateString(), $e->id])
                ->take(self::LIMITE_MEDICOES)
                ->map(fn(ExecucaoObra $e) => [
                    'id'                   => $e->id,
                    'data_medicao'         => $e->data_medicao?->toDateString(),
                    'valor_medido'         => (float) $e->valor_medido,
                    'percentual_executado' => $e->percentual_executado !== null ? (float) $e->percentual_executado : null,
                ])
                ->values(),
        ]);
    }
}
