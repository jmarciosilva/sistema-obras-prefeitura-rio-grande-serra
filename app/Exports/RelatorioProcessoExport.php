<?php

namespace App\Exports;

use App\Exports\Sheets\Processos\PendenciasSheet;
use App\Exports\Sheets\Processos\PorFaseSheet;
use App\Exports\Sheets\Processos\PorTipoSheet;
use App\Exports\Sheets\Processos\ProcessosSheet;
use App\Exports\Sheets\Processos\ResumoSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class RelatorioProcessoExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(private array $dados)
    {
    }

    public function sheets(): array
    {
        return [
            'Resumo'      => new ResumoSheet($this->dados),
            'Processos'   => new ProcessosSheet($this->dados),
            'Por Fase'    => new PorFaseSheet($this->dados),
            'Por Tipo'    => new PorTipoSheet($this->dados),
            'Pendências'  => new PendenciasSheet($this->dados),
        ];
    }
}
