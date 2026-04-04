<?php
// ═══════════════════════════════════════════════════════════════
// app/Exports/Sheets/ObrasSheet.php
// ═══════════════════════════════════════════════════════════════
namespace App\Exports\Sheets;
 
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ObrasSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(private array $dados) {}

    public function title(): string
    {
        return 'Obras';
    }

    public function array(): array
    {
        $cabecalho = [
            'ID',
            'Descrição',
            'Status',
            'Endereço',
            'Processo',
            'Contratos',
            'Valor Contratado',
            'Valor Medido',
            'Saldo',
            '% Executado',
        ];

        $linhas = $this->dados['obras']->map(fn($o) => [
            $o->id,
            $o->descricao,
            $o->status->nome ?? '—',
            $o->endereco ?? '—',
            $o->processo_execucao ?? '—',
            $o->contratos->count(),
            number_format($o->contratos->sum('valor_contrato'), 2, ',', '.'),
            number_format($o->valor_medido, 2, ',', '.'),
            number_format($o->saldo_contratual, 2, ',', '.'),
            number_format($o->percentual_executado, 1) . '%',
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
            'A' => 6,
            'B' => 60,
            'C' => 18,
            'D' => 30,
            'E' => 18,
            'F' => 10,
            'G' => 20,
            'H' => 20,
            'I' => 20,
            'J' => 14,
        ];
    }
}
