<?php

namespace App\Observers;

use App\Models\ExecucaoObra;
use App\Services\AuditoriaService;

/** Auditoria de Medições (execucao_obras). */
class ExecucaoObraObserver
{
    public function __construct(private AuditoriaService $auditoria) {}

    public function created(ExecucaoObra $medicao): void
    {
        $this->auditoria->criado($medicao, 'Medição criada: ' . self::nome($medicao));
    }

    public function updated(ExecucaoObra $medicao): void
    {
        $this->auditoria->alterado($medicao, 'Medição atualizada: ' . self::nome($medicao));
    }

    public function deleted(ExecucaoObra $medicao): void
    {
        $this->auditoria->excluido($medicao, 'Medição excluída: ' . self::nome($medicao));
    }

    /** "10/06/2026 — R$ 90.732,76" */
    private static function nome(ExecucaoObra $medicao): string
    {
        $data  = $medicao->data_medicao?->format('d/m/Y') ?? 'sem data';
        $valor = 'R$ ' . number_format((float) $medicao->valor_medido, 2, ',', '.');

        return "{$data} — {$valor}";
    }
}
