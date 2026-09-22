<?php

namespace App\Observers;

use App\Models\Contrato;
use App\Services\AuditoriaService;

class ContratoObserver
{
    /** Medições que o MySQL apaga em cascata (sem eventos Eloquent). */
    private static array $cascata = [];

    public function __construct(private AuditoriaService $auditoria) {}

    public function created(Contrato $contrato): void
    {
        $this->auditoria->criado($contrato, 'Contrato criado: ' . self::nome($contrato));
    }

    public function updated(Contrato $contrato): void
    {
        $this->auditoria->alterado($contrato, 'Contrato atualizado: ' . self::nome($contrato));
    }

    public function deleting(Contrato $contrato): void
    {
        self::$cascata[spl_object_id($contrato)] = $contrato->execucoes()->count();
    }

    public function deleted(Contrato $contrato): void
    {
        $medicoes = self::$cascata[spl_object_id($contrato)] ?? 0;
        unset(self::$cascata[spl_object_id($contrato)]);

        $descricao = 'Contrato excluído: ' . self::nome($contrato);
        if ($medicoes) {
            $descricao .= " (removidas junto: {$medicoes} medição(ões))";
        }

        $this->auditoria->excluido($contrato, $descricao);
    }

    private static function nome(Contrato $contrato): string
    {
        return $contrato->numero_contrato_ano ?: "#{$contrato->getKey()}";
    }
}
