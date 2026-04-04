<?php
// ═══════════════════════════════════════════════════════════════
// app/Exports/Sheets/ResumoSheet.php
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
 
class ResumoSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(private array $dados) {}
 
    public function title(): string { return 'Resumo'; }
 
    public function array(): array
    {
        $d = $this->dados;
        return [
            ['RELATÓRIO DE OBRAS — PREFEITURA DE RIO GRANDE DA SERRA/SP'],
            ['Gerado em: ' . now()->format('d/m/Y H:i')],
            [],
            ['INDICADORES GERAIS'],
            ['Total de Obras',       $d['obras']->count()],
            ['Valor Contratado',     'R$ ' . number_format($d['valorContratadoTotal'], 2, ',', '.')],
            ['Valor Medido',         'R$ ' . number_format($d['valorMedidoTotal'], 2, ',', '.')],
            ['Saldo a Medir',        'R$ ' . number_format($d['saldoTotal'], 2, ',', '.')],
            ['% Executado',          number_format($d['percentualGeral'], 1) . '%'],
            [],
            ['OBRAS POR STATUS'],
            ['Status', 'Quantidade'],
            ...$d['porStatus']->map(fn($s) => [$s['nome'], $s['total']])->toArray(),
        ];
    }
 
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            4 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']]],
            11 => ['font' => ['bold' => true], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']]],
            12 => ['font' => ['bold' => true]],
        ];
    }
 
    public function columnWidths(): array
    {
        return ['A' => 30, 'B' => 25];
    }
}