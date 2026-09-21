<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Contrato;
use App\Services\DashboardObrasService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Item da listagem de contratos (API mobile).
 * Espera obra, empresa e withSum('execucoes', 'valor_medido') carregados.
 */
class ContratoResource extends JsonResource
{
    public const SITUACAO_VENCIDO        = 'vencido';
    public const SITUACAO_VENCE_EM_BREVE = 'vence_em_breve';
    public const SITUACAO_VIGENTE        = 'vigente';
    public const SITUACAO_SEM_VIGENCIA   = 'sem_vigencia';

    public const SITUACOES = [
        self::SITUACAO_VENCIDO,
        self::SITUACAO_VENCE_EM_BREVE,
        self::SITUACAO_VIGENTE,
        self::SITUACAO_SEM_VIGENCIA,
    ];

    /**
     * Mesmas regras do dashboard: vencido = Contrato::estaVencido();
     * vence em breve = vigência entre agora e +30 dias (alerta "contratos_vencendo").
     *
     * Não usa Contrato::venceEm(): com Carbon 3 o diffInDays() tem sinal e o
     * método retorna true para qualquer vigência futura.
     */
    public static function situacaoVigencia(Contrato $contrato): string
    {
        if (! $contrato->vigencia_contrato) {
            return self::SITUACAO_SEM_VIGENCIA;
        }

        if ($contrato->estaVencido()) {
            return self::SITUACAO_VENCIDO;
        }

        $limite = now()->addDays(DashboardObrasService::DIAS_CONTRATO_VENCENDO);

        return $contrato->vigencia_contrato->lte($limite)
            ? self::SITUACAO_VENCE_EM_BREVE
            : self::SITUACAO_VIGENTE;
    }

    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'numero_contrato_ano' => $this->numero_contrato_ano,
            'processo_licitacao'  => $this->processo_licitacao,
            'obra'                => $this->obra ? [
                'id'        => $this->obra->id,
                'descricao' => $this->obra->descricao,
            ] : null,
            'empresa'             => $this->dadosEmpresa(),
            ...$this->financeiro(),
            'data_assinatura'     => $this->data_assinatura?->toDateString(),
            'ordem_inicio'        => $this->ordem_inicio?->toDateString(),
            'vigencia_contrato'   => $this->vigencia_contrato?->toDateString(),
            'situacao_vigencia'   => self::situacaoVigencia($this->resource),
        ];
    }

    protected function dadosEmpresa(): ?array
    {
        return $this->empresa ? [
            'id'            => $this->empresa->id,
            'razao_social'  => $this->empresa->razao_social,
            'nome_fantasia' => $this->empresa->nome_fantasia,
        ] : null;
    }

    /**
     * Mesma regra do sistema web (ExecucaoObraController / accessors de Obra):
     * medido = soma das medições; saldo nunca negativo; percentual limitado a 100.
     */
    protected function financeiro(): array
    {
        $contratado = (float) ($this->valor_contrato ?? 0);
        $medido     = (float) ($this->execucoes_sum_valor_medido ?? 0);

        return [
            'valor_contrato'       => round($contratado, 2),
            'valor_medido'         => round($medido, 2),
            'saldo'                => round(max($contratado - $medido, 0), 2),
            'percentual_executado' => $contratado > 0
                ? round(min(($medido / $contratado) * 100, 100), 2)
                : 0.0,
        ];
    }
}
