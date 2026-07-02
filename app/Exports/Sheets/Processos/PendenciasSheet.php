<?php

namespace App\Exports\Sheets\Processos;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PendenciasSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(private array $dados)
    {
    }

    public function title(): string
    {
        return 'Pendências';
    }

    public function array(): array
    {
        $cabecalho = ['Nº do Processo', 'Requerente', 'Fase Atual', 'Motivo da Pendência', 'Última Atualização'];

        $linhas = $this->dados['comPendencia']->map(fn($p) => [
            $p->processo_numero,
            $p->requerente,
            $p->faseAtual->nome ?? '—',
            $p->motivo_pendencia,
            $p->updated_at->format('d/m/Y'),
        ])->values()->toArray();

        return [$cabecalho, ...$linhas];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B91C1C']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 16, 'B' => 35, 'C' => 28, 'D' => 55, 'E' => 16];
    }
}
