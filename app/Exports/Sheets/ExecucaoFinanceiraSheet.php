<?php
// ═══════════════════════════════════════════════════════════════
// app/Exports/Sheets/ExecucaoFinanceiraSheet.php
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

class ExecucaoFinanceiraSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(private array $dados) {}

    public function title(): string
    {
        return 'Execução Financeira';
    }

    public function array(): array
    {
        $cabecalho = [
            'Obra',
            'Valor Contratado (R$)',
            'Valor Medido (R$)',
            'Saldo (R$)',
            '% Executado',
            'Situação',
        ];

        $linhas = $this->dados['execucaoFinanceira']->map(fn($item) => [
            \Illuminate\Support\Str::limit($item['obra']->descricao, 60),
            number_format($item['valor_contratado'], 2, ',', '.'),
            number_format($item['valor_medido'], 2, ',', '.'),
            number_format($item['saldo'], 2, ',', '.'),
            number_format($item['percentual'], 1) . '%',
            match (true) {
                $item['percentual'] >= 100 => 'Concluída',
                $item['percentual'] >= 75  => 'Avançada',
                $item['percentual'] >= 40  => 'Em andamento',
                $item['percentual'] > 0    => 'Inicial',
                default                    => 'Não iniciada',
            },
        ])->toArray();

        return [$cabecalho, ...$linhas];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '065F46']],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return ['A' => 60, 'B' => 22, 'C' => 22, 'D' => 22, 'E' => 14, 'F' => 16];
    }
}
