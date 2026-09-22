<?php

namespace App\Observers;

use App\Models\ExecucaoObra;
use App\Models\Obra;
use App\Services\AuditoriaService;
use Illuminate\Support\Str;

/**
 * Auditoria de Obras. Vínculos de convênios (pivot) não disparam eventos do
 * Model: são auditados no ObraController.
 */
class ObraObserver
{
    /** Contratos/medições que o MySQL apaga em cascata (sem eventos Eloquent). */
    private static array $cascata = [];

    public function __construct(private AuditoriaService $auditoria) {}

    public function created(Obra $obra): void
    {
        $this->auditoria->criado($obra, 'Obra criada: ' . self::nome($obra));
    }

    public function updated(Obra $obra): void
    {
        $this->auditoria->alterado($obra, 'Obra atualizada: ' . self::nome($obra));
    }

    public function deleting(Obra $obra): void
    {
        $contratos = $obra->contratos()->pluck('id');
        self::$cascata[spl_object_id($obra)] = [
            $contratos->count(),
            ExecucaoObra::whereIn('contrato_id', $contratos)->count(),
        ];
    }

    public function deleted(Obra $obra): void
    {
        [$contratos, $medicoes] = self::$cascata[spl_object_id($obra)] ?? [0, 0];
        unset(self::$cascata[spl_object_id($obra)]);

        $descricao = 'Obra excluída: ' . self::nome($obra);
        if ($contratos || $medicoes) {
            $descricao .= " (removidos junto: {$contratos} contrato(s) e {$medicoes} medição(ões))";
        }

        $this->auditoria->excluido($obra, $descricao);
    }

    private static function nome(Obra $obra): string
    {
        return Str::limit((string) $obra->descricao, 80);
    }
}
