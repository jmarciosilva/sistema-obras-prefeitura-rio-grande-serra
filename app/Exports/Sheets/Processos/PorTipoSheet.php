<?php

namespace App\Exports\Sheets\Processos;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PorTipoSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(private array $dados)
    {
    }

    public function title(): string
    {
        return 'Por Tipo';
    }

    public function array(): array
    {
        $total = $this->dados['porTipo']->sum('total');

        $cabecalho = ['Tipo de Processo', 'Quantidade', '% do Total'];

        $linhas = $this->dados['porTipo']->map(fn($t) => [
            $t['nome'],
            $t['total'],
            $total > 0 ? number_format(($t['total'] / $total) * 100, 1) . '%' : '0%',
        ])->toArray();

        return [$cabecalho, ...$linhas];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E40AF']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 45, 'B' => 15, 'C' => 15];
    }
}
