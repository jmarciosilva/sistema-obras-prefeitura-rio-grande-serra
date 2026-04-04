<?php
// ═══════════════════════════════════════════════════════════════
// app/Exports/Sheets/ContratosVencendoSheet.php
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

class ContratosVencendoSheet implements FromArray, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(private array $dados) {}
 
    public function title(): string { return 'Contratos Vencendo'; }
 
    public function array(): array
    {
        $vencendo = [
            ['CONTRATOS VENCENDO EM ATÉ ' . $this->dados['vencimentoDias'] . ' DIAS'],
            ['Contrato', 'Obra', 'Empresa', 'Vigência', 'Dias Restantes', 'Valor (R$)'],
            ...$this->dados['contratosVencendo']->map(fn($item) => [
                $item['contrato']->numero_contrato_ano ?? '#' . $item['contrato']->id,
                \Illuminate\Support\Str::limit($item['contrato']->obra->descricao ?? '—', 50),
                $item['contrato']->empresa->razao_social ?? '—',
                $item['contrato']->vigencia_contrato?->format('d/m/Y') ?? '—',
                $item['dias_restantes'],
                number_format($item['contrato']->valor_contrato ?? 0, 2, ',', '.'),
            ])->toArray(),
        ];
 
        $vencidos = [
            [],
            ['CONTRATOS VENCIDOS'],
            ['Contrato', 'Obra', 'Empresa', 'Venceu em', 'Valor (R$)'],
            ...$this->dados['contratosVencidos']->map(fn($c) => [
                $c->numero_contrato_ano ?? '#' . $c->id,
                \Illuminate\Support\Str::limit($c->obra->descricao ?? '—', 50),
                $c->empresa->razao_social ?? '—',
                $c->vigencia_contrato?->format('d/m/Y') ?? '—',
                number_format($c->valor_contrato ?? 0, 2, ',', '.'),
            ])->toArray(),
        ];
 
        return array_merge($vencendo, $vencidos);
    }
 
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
            2 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B45309']],
            ],
        ];
    }
 
    public function columnWidths(): array
    {
        return ['A' => 18, 'B' => 50, 'C' => 35, 'D' => 14, 'E' => 16, 'F' => 20];
    }
}
