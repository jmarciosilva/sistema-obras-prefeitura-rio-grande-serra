<?php

namespace App\Exports\Sheets\Processos;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class ProcessosSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(private array $dados)
    {
    }

    public function title(): string
    {
        return 'Processos';
    }

    public function array(): array
    {
        $cabecalho = [
            'Nº do Processo', 'Requerente', 'Tipo', 'Fase Atual', 'Responsável Técnico',
            'Situação', 'Data de Entrada', 'Endereço', 'Motivo da Pendência',
        ];

        $linhas = $this->dados['processos']->map(fn($p) => [
            $p->processo_numero,
            $p->requerente,
            $p->tipoProcesso->nome ?? '—',
            $p->faseAtual->nome ?? '—',
            $p->responsavelTecnico->nome ?? '—',
            $p->situacao_label,
            $p->data_entrada?->format('d/m/Y') ?? '—',
            $p->endereco_completo ?? '—',
            $p->motivo_pendencia ?? '',
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
        return [
            'A' => 16, 'B' => 35, 'C' => 30, 'D' => 28, 'E' => 25,
            'F' => 12, 'G' => 14, 'H' => 40, 'I' => 45,
        ];
    }
}
