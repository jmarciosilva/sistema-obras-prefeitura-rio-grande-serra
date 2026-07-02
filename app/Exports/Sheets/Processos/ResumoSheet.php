<?php

namespace App\Exports\Sheets\Processos;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ResumoSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(private array $dados)
    {
    }

    public function title(): string
    {
        return 'Resumo';
    }

    public function array(): array
    {
        $d = $this->dados;

        return [
            ['RELATÓRIO DE PROCESSOS ADMINISTRATIVOS — PREFEITURA DE RIO GRANDE DA SERRA/SP'],
            ['Gerado em: ' . now()->format('d/m/Y H:i')],
            [],
            ['INDICADORES GERAIS'],
            ['Total de Processos (filtro aplicado)', $d['totalFiltrado']],
            ['Abertos',                              $d['abertosFiltrado']],
            ['Com Pendência Registrada',              $d['comPendencia']->count()],
            [],
            ['PROCESSOS POR FASE'],
            ['Fase', 'Quantidade'],
            ...$d['porFase']->map(fn($f) => [$f['nome'], $f['total']])->toArray(),
            [],
            ['PROCESSOS POR TIPO'],
            ['Tipo', 'Quantidade'],
            ...$d['porTipo']->map(fn($t) => [$t['nome'], $t['total']])->toArray(),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            4 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']]],
            9 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']]],
            10 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 45, 'B' => 15];
    }
}
