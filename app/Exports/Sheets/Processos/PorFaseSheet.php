<?php

namespace App\Exports\Sheets\Processos;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PorFaseSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(private array $dados)
    {
    }

    public function title(): string
    {
        return 'Por Fase';
    }

    public function array(): array
    {
        $total = $this->dados['porFase']->sum('total');

        $cabecalho = ['Fase', 'Quantidade', '% do Total'];

        $linhas = $this->dados['porFase']->map(fn($f) => [
            $f['nome'],
            $f['total'],
            $total > 0 ? number_format(($f['total'] / $total) * 100, 1) . '%' : '0%',
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
        return ['A' => 40, 'B' => 15, 'C' => 15];
    }
}
