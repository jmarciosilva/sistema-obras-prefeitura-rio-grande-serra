<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\Exportable;

class RelatorioExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(
        private array $dados,
        private array $filtros
    ) {}

    public function sheets(): array
    {
        return [
            'Resumo'              => new Sheets\ResumoSheet($this->dados),
            'Obras'               => new Sheets\ObrasSheet($this->dados),
            'Execução Financeira' => new Sheets\ExecucaoFinanceiraSheet($this->dados),
            'Contratos Vencendo'  => new Sheets\ContratosVencendoSheet($this->dados),
            'Por Empresa'         => new Sheets\PorEmpresaSheet($this->dados),
        ];
    }
}
