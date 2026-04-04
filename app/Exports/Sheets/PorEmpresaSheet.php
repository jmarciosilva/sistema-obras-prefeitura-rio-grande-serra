<?php
// ═══════════════════════════════════════════════════════════════
// app/Exports/Sheets/PorEmpresaSheet.php
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

class PorEmpresaSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(private array $dados) {}
 
    public function title(): string { return 'Por Empresa'; }
 
    public function array(): array
    {
        $cabecalho = ['Empresa', 'CNPJ', 'Total de Contratos', 'Valor Total (R$)'];
 
        $linhas = $this->dados['porEmpresa']->map(fn($item) => [
            $item['empresa']->razao_social,
            $item['empresa']->cnpj,
            $item['total_contratos'],
            number_format($item['valor_total'], 2, ',', '.'),
        ])->toArray();
 
        return [$cabecalho, ...$linhas];
    }
 
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4338CA']],
            ],
        ];
    }
 
    public function columnWidths(): array
    {
        return ['A' => 45, 'B' => 22, 'C' => 20, 'D' => 22];
    }
}