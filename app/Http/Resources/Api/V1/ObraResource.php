<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Item da listagem de obras. Espera status e contratos.execucoes carregados.
 */
class ObraResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'descricao'            => $this->descricao,
            'endereco'             => $this->endereco,
            'status'               => $this->whenLoaded('status', fn() => $this->status ? [
                'id'   => $this->status->id,
                'nome' => $this->status->nome,
                'cor'  => $this->status->cor,
            ] : null),
            'valor_contratado'     => round((float) $this->valor_contratado, 2),
            'valor_medido'         => round((float) $this->valor_medido, 2),
            'saldo'                => round((float) $this->saldo_contratual, 2),
            'percentual_executado' => round((float) $this->percentual_executado, 2),
        ];
    }
}
