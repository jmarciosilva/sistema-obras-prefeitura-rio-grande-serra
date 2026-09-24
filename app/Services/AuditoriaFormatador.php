<?php

namespace App\Services;

use App\Models\Auditoria;
use App\Models\Contrato;
use App\Models\Convenio;
use App\Models\DemandaProposta;
use App\Models\Empresa;
use App\Models\Obra;
use App\Models\StatusObra;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * Transforma os dados de uma auditoria em linhas legíveis
 * ("Status | Em Planejamento | Em Execução"), sem JSON cru na tela.
 * Só é usado na tela de detalhe (uma consulta por tipo de relacionamento).
 */
class AuditoriaFormatador
{
    public const ROTULOS = [
        // Obra
        'descricao'           => 'Descrição',
        'endereco'            => 'Endereço',
        'processo_execucao'   => 'Processo de execução',
        'observacoes'         => 'Observações',
        'status_obra_id'      => 'Status',
        'demanda_proposta_id' => 'Demanda / proposta',
        'convenios'           => 'Convênios',
        // Contrato
        'obra_id'             => 'Obra',
        'empresa_id'          => 'Empresa',
        'processo_licitacao'  => 'Processo licitatório',
        'numero_contrato_ano' => 'Número do contrato',
        'data_assinatura'     => 'Data de assinatura',
        'ordem_inicio'        => 'Ordem de início',
        'vigencia_contrato'   => 'Vigência',
        'valor_contrato'      => 'Valor contratado',
        // Medição
        'contrato_id'          => 'Contrato',
        'data_medicao'         => 'Data da medição',
        'valor_medido'         => 'Valor medido',
        'saldo_contratual'     => 'Saldo contratual',
        'percentual_executado' => 'Percentual executado',
        'observacao'           => 'Observação',
        'responsaveis'         => 'Responsáveis',
        'documentos'           => 'Documentos anexados',
    ];

    private const MOEDA = ['valor_contrato', 'valor_medido', 'saldo_contratual'];
    private const DATAS = ['data_assinatura', 'ordem_inicio', 'vigencia_contrato', 'data_medicao'];

    /** Campo de chave estrangeira → [Model, coluna exibida]. */
    private const RELACOES = [
        'status_obra_id'      => [StatusObra::class, 'nome'],
        'empresa_id'          => [Empresa::class, 'razao_social'],
        'obra_id'             => [Obra::class, 'descricao'],
        'contrato_id'         => [Contrato::class, 'numero_contrato_ano'],
        'demanda_proposta_id' => [DemandaProposta::class, 'numero_demanda'],
        'convenios'           => [Convenio::class, 'descricao'],
    ];

    /** Ordem dos campos na tela: a mesma de ROTULOS; desconhecidos no fim. */
    public function linhas(Auditoria $auditoria): array
    {
        $antes  = $auditoria->dados_antes ?? [];
        $depois = $auditoria->dados_depois ?? [];
        $campos = array_unique([...array_keys($antes), ...array_keys($depois)]);

        $ordem = array_flip(array_keys(self::ROTULOS));
        usort($campos, fn($a, $b) => ($ordem[$a] ?? 999) <=> ($ordem[$b] ?? 999));

        $nomes = $this->resolverNomes($antes, $depois);

        return array_map(fn($campo) => [
            'rotulo' => self::rotulo($campo),
            'antes'  => array_key_exists($campo, $antes) ? $this->valor($campo, $antes[$campo], $nomes) : null,
            'depois' => array_key_exists($campo, $depois) ? $this->valor($campo, $depois[$campo], $nomes) : null,
        ], $campos);
    }

    public static function rotulo(string $campo): string
    {
        return self::ROTULOS[$campo] ?? Str::of($campo)->replace('_', ' ')->ucfirst()->toString();
    }

    private function valor(string $campo, mixed $valor, array $nomes): string
    {
        if (isset(self::RELACOES[$campo])) {
            $ids = is_array($valor) ? $valor : [$valor];
            $ids = array_filter($ids, fn($id) => $id !== null && $id !== '');
            if ($ids === []) {
                return is_array($valor) ? 'Nenhum' : '—';
            }

            return collect($ids)
                ->map(fn($id) => $nomes[$campo][$id] ?? "#{$id} (não encontrado)")
                ->implode(', ');
        }

        if ($valor === null || $valor === '') {
            return '—';
        }

        if (in_array($campo, self::MOEDA, true)) {
            return 'R$ ' . number_format((float) $valor, 2, ',', '.');
        }

        if ($campo === 'percentual_executado') {
            return number_format((float) $valor, 2, ',', '.') . '%';
        }

        if (in_array($campo, self::DATAS, true)) {
            try {
                return Carbon::parse($valor)->format('d/m/Y');
            } catch (\Throwable) {
                return (string) $valor;
            }
        }

        // Listas de textos (ex.: responsáveis, documentos): uma por linha
        if (is_array($valor) && array_is_list($valor) && collect($valor)->every(fn($v) => is_scalar($v))) {
            return $valor === [] ? 'Nenhum' : implode("\n", $valor);
        }

        return is_array($valor) ? json_encode($valor, JSON_UNESCAPED_UNICODE) : (string) $valor;
    }

    /** Uma consulta por relacionamento presente nos dados. */
    private function resolverNomes(array $antes, array $depois): array
    {
        $nomes = [];
        foreach (self::RELACOES as $campo => [$model, $coluna]) {
            $ids = collect([$antes[$campo] ?? null, $depois[$campo] ?? null])
                ->flatten()
                ->filter(fn($id) => $id !== null && $id !== '')
                ->unique()
                ->values();

            if ($ids->isEmpty()) {
                continue;
            }

            $nomes[$campo] = $model::whereIn('id', $ids)
                ->pluck($coluna, 'id')
                ->map(fn($nome, $id) => $nome ? Str::limit((string) $nome, 90) : "#{$id}")
                ->all();
        }

        return $nomes;
    }
}
