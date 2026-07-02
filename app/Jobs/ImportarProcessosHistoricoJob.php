<?php

namespace App\Jobs;

use App\Models\Desarquivamento;
use App\Models\FaseProcesso;
use App\Models\Processo;
use App\Models\RenovacaoAlvara;
use App\Models\TipoProcesso;
use App\Models\Tramite;
use App\Services\Importacao\NumeroProcessoNormalizer;
use App\Services\Importacao\ResponsavelTecnicoMatcher;
use App\Services\Importacao\TipoProcessoMatcher;
use App\Services\Importacao\TramiteCellParser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Importa o histórico de processos administrativos da planilha legada
 * (CONTROLE_PROCESSOS.xlsx — ver roadmap-modulo-processos-administrativos.md, Fase 7.2).
 *
 * Idempotente: pode ser reprocessada com segurança (não duplica processos nem
 * trâmites já importados — ver `processo_numero_normalizado` e `importacao_ref`).
 * Erros são isolados por linha: um registro malformado é logado e pulado, sem
 * abortar a importação inteira.
 *
 * Não tenta categorizar 100% do texto livre histórico automaticamente (fase e tipo
 * de processo "chutados" de forma conservadora, com fallback explícito para revisão
 * manual) — decisão deliberada do roadmap, não uma limitação a "corrigir depois".
 */
class ImportarProcessosHistoricoJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800;

    public int $tries = 1;

    private const LINHAS_VAZIAS_PARA_PARAR = 30;

    private const MESES = [
        'JANEIRO', 'FEVEREIRO', 'MARÇO', 'ABRIL', 'MAIO', 'JUNHO',
        'JULHO', 'AGOSTO', 'SETEMBRO', 'OUTUBRO', 'NOVEMBRO', 'DEZEMBRO',
    ];

    private int $faseAClassificarId;

    private array $resumo = [];

    public function __construct(private readonly string $caminhoArquivo)
    {
    }

    public function handle(): void
    {
        if (! is_file($this->caminhoArquivo)) {
            throw new \RuntimeException("Arquivo não encontrado: {$this->caminhoArquivo}");
        }

        ResponsavelTecnicoMatcher::resetCache();

        $this->faseAClassificarId = FaseProcesso::where('nome', 'A Classificar')->value('id')
            ?? throw new \RuntimeException('Fase "A Classificar" não encontrada — rode o FaseProcessoSeeder.');

        $spreadsheet = IOFactory::load($this->caminhoArquivo);

        $this->importarSheetPadrao($spreadsheet, 'CONTROLE', 'controle');
        $this->importarSheetPadrao($spreadsheet, 'ÁGUA E LUZ', 'agua_e_luz', 'Ofício de Ligação de Água/Energia');
        $this->importarDesmembramentos($spreadsheet);
        $this->importarSisobra($spreadsheet);
        $this->importarDesarquivamento($spreadsheet);
        $this->importarRenovacao($spreadsheet);

        Log::info('Importação de processos administrativos concluída.', $this->resumo);
    }

    public function resumo(): array
    {
        return $this->resumo;
    }

    // ─────────────────────────────────────────────────────────────
    // ABAS "PADRÃO" — CONTROLE e ÁGUA E LUZ (mesmo layout de colunas)
    // A | B | C | D | E | F | G..? = PROCESSO|NOME|ASSUNTO|RESPONSÁVEL|ENDEREÇO|DATA ENTRADA|trâmites
    // ─────────────────────────────────────────────────────────────
    private function importarSheetPadrao(
        Spreadsheet $spreadsheet,
        string $nomeAba,
        string $origem,
        ?string $tipoForcadoNome = null,
    ): void {
        $sheet = $this->buscarSheet($spreadsheet, $nomeAba);

        if (! $sheet) {
            Log::warning("Importação de processos: aba \"{$nomeAba}\" não encontrada — pulando.");
            return;
        }

        $tipoForcadoId = $tipoForcadoNome
            ? TipoProcesso::where('nome', $tipoForcadoNome)->value('id')
            : null;

        $colunasTramite = $this->detectarColunasTramite($sheet, 1, 7);

        $stats = ['criados' => 0, 'atualizados' => 0, 'erros' => 0, 'tramites' => 0];
        $linhasVazias = 0;
        $linha = 2;

        while ($linhasVazias < self::LINHAS_VAZIAS_PARA_PARAR) {
            $processoNumero = trim((string) $sheet->getCellByColumnAndRow(1, $linha)->getFormattedValue());

            if ($processoNumero === '') {
                $linhasVazias++;
                $linha++;
                continue;
            }

            $linhasVazias = 0;

            try {
                DB::transaction(function () use ($sheet, $linha, $processoNumero, $origem, $tipoForcadoId, $colunasTramite, &$stats) {
                    $assunto     = trim((string) $sheet->getCellByColumnAndRow(3, $linha)->getFormattedValue());
                    $responsavel = trim((string) $sheet->getCellByColumnAndRow(4, $linha)->getFormattedValue());
                    $endereco    = trim((string) $sheet->getCellByColumnAndRow(5, $linha)->getFormattedValue());

                    $dataEntradaCel = $this->lerCelula($sheet, 6, $linha);
                    $dataEntrada    = TramiteCellParser::parse($dataEntradaCel['raw'], $dataEntradaCel['formatado'])['data'] ?? null;

                    $processo = $this->criarOuAtualizarProcesso(
                        processoNumero: $processoNumero,
                        requerente: trim((string) $sheet->getCellByColumnAndRow(2, $linha)->getFormattedValue()),
                        endereco: $endereco,
                        tipoProcessoId: $tipoForcadoId ?? TipoProcessoMatcher::match($assunto),
                        responsavelTecnicoId: ResponsavelTecnicoMatcher::matchOuCriar($responsavel),
                        dataEntrada: $dataEntrada,
                        origem: $origem,
                        criados: $stats['criados'],
                        atualizados: $stats['atualizados'],
                    );

                    $stats['tramites'] += $this->importarTramitesDaLinha($sheet, $linha, $colunasTramite, $processo, $origem);
                });
            } catch (\Throwable $e) {
                $stats['erros']++;
                Log::error("Importação [{$nomeAba}] linha {$linha}: falha ao processar.", [
                    'processo_numero' => $processoNumero,
                    'erro'            => $e->getMessage(),
                ]);
            }

            $linha++;
        }

        $this->resumo[$origem] = $stats;
    }

    // ─────────────────────────────────────────────────────────────
    // DESMEMBRAMENTOS_ACIMA_DE_02_LOT — sem linha de cabeçalho real
    // A=processo | B=nome | C=assunto | D=endereço | E=data (texto livre/corrompido) | F,G = observações
    // ─────────────────────────────────────────────────────────────
    private function importarDesmembramentos(Spreadsheet $spreadsheet): void
    {
        $sheet = $this->buscarSheet($spreadsheet, 'DESMEMBRAMENTOS_ACIMA_DE_02_LOT');

        if (! $sheet) {
            Log::warning('Importação de processos: aba "DESMEMBRAMENTOS_ACIMA_DE_02_LOT" não encontrada — pulando.');
            return;
        }

        $tipoId = TipoProcesso::where('nome', 'Alvará de Desdobro/Unificação/Desmembramento')->value('id');

        $stats = ['criados' => 0, 'atualizados' => 0, 'erros' => 0, 'tramites' => 0];
        $linhasVazias = 0;
        $linha = 2; // linha 1 é só um título, não cabeçalho de colunas

        while ($linhasVazias < self::LINHAS_VAZIAS_PARA_PARAR) {
            $processoNumero = trim((string) $sheet->getCellByColumnAndRow(1, $linha)->getFormattedValue());

            if ($processoNumero === '') {
                $linhasVazias++;
                $linha++;
                continue;
            }

            $linhasVazias = 0;

            try {
                DB::transaction(function () use ($sheet, $linha, $processoNumero, $tipoId, &$stats) {
                    $dataCel = $this->lerCelula($sheet, 5, $linha);
                    $data    = TramiteCellParser::parse($dataCel['raw'], $dataCel['formatado'])['data'] ?? null;

                    $processo = $this->criarOuAtualizarProcesso(
                        processoNumero: $processoNumero,
                        requerente: trim((string) $sheet->getCellByColumnAndRow(2, $linha)->getFormattedValue()),
                        endereco: trim((string) $sheet->getCellByColumnAndRow(4, $linha)->getFormattedValue()),
                        tipoProcessoId: $tipoId,
                        responsavelTecnicoId: null,
                        dataEntrada: $data,
                        origem: 'desmembramento_2_lotes',
                        criados: $stats['criados'],
                        atualizados: $stats['atualizados'],
                    );

                    $obs = trim(implode(' — ', array_filter([
                        trim((string) $sheet->getCellByColumnAndRow(6, $linha)->getFormattedValue()),
                        trim((string) $sheet->getCellByColumnAndRow(7, $linha)->getFormattedValue()),
                    ])));

                    if ($obs !== '') {
                        Tramite::updateOrCreate(
                            ['importacao_ref' => "DESMEMBRAMENTOS#L{$linha}"],
                            [
                                'processo_id' => $processo->id,
                                'data'        => null,
                                'descricao'   => $obs,
                                'fase_id'     => $this->faseAClassificarId,
                            ]
                        );
                        $stats['tramites']++;
                    }
                });
            } catch (\Throwable $e) {
                $stats['erros']++;
                Log::error("Importação [DESMEMBRAMENTOS] linha {$linha}: falha ao processar.", [
                    'processo_numero' => $processoNumero,
                    'erro'            => $e->getMessage(),
                ]);
            }

            $linha++;
        }

        $this->resumo['desmembramentos_2_lotes'] = $stats;
    }

    // ─────────────────────────────────────────────────────────────
    // SISOBRA — flag em processos, não tabela dedicada (ver roadmap 3.1)
    // A=processo | B=nome | C=assunto | D=data entrada | E=1º trâmite
    // ─────────────────────────────────────────────────────────────
    private function importarSisobra(Spreadsheet $spreadsheet): void
    {
        $sheet = $this->buscarSheet($spreadsheet, ' SISOBRA ') ?? $this->buscarSheet($spreadsheet, 'SISOBRA');

        if (! $sheet) {
            Log::warning('Importação de processos: aba "SISOBRA" não encontrada — pulando.');
            return;
        }

        $colunasTramite = $this->detectarColunasTramite($sheet, 1, 5);

        $stats = ['criados' => 0, 'atualizados' => 0, 'erros' => 0, 'tramites' => 0];
        $linhasVazias = 0;
        $linha = 2;

        while ($linhasVazias < self::LINHAS_VAZIAS_PARA_PARAR) {
            $processoNumero = trim((string) $sheet->getCellByColumnAndRow(1, $linha)->getFormattedValue());

            if ($processoNumero === '') {
                $linhasVazias++;
                $linha++;
                continue;
            }

            $linhasVazias = 0;

            try {
                DB::transaction(function () use ($sheet, $linha, $processoNumero, $colunasTramite, &$stats) {
                    $assunto = trim((string) $sheet->getCellByColumnAndRow(3, $linha)->getFormattedValue());

                    $dataCel = $this->lerCelula($sheet, 4, $linha);
                    $data    = TramiteCellParser::parse($dataCel['raw'], $dataCel['formatado'])['data'] ?? null;

                    $processo = $this->criarOuAtualizarProcesso(
                        processoNumero: $processoNumero,
                        requerente: trim((string) $sheet->getCellByColumnAndRow(2, $linha)->getFormattedValue()),
                        endereco: null,
                        tipoProcessoId: TipoProcessoMatcher::match($assunto),
                        responsavelTecnicoId: null,
                        dataEntrada: $data,
                        origem: 'sisobra',
                        criados: $stats['criados'],
                        atualizados: $stats['atualizados'],
                        apenasPreencherVazios: true,
                    );

                    $processo->sisobra = true;
                    $processo->sisobra_data_cadastro ??= $data;
                    $processo->save();

                    $stats['tramites'] += $this->importarTramitesDaLinha($sheet, $linha, $colunasTramite, $processo, 'sisobra');
                });
            } catch (\Throwable $e) {
                $stats['erros']++;
                Log::error("Importação [SISOBRA] linha {$linha}: falha ao processar.", [
                    'processo_numero' => $processoNumero,
                    'erro'            => $e->getMessage(),
                ]);
            }

            $linha++;
        }

        $this->resumo['sisobra'] = $stats;
    }

    // ─────────────────────────────────────────────────────────────
    // DESARQUIVAMENTO — tabela dedicada
    // A=processo | B=nome | C=assunto | D=data solicitação | E=motivo
    // ─────────────────────────────────────────────────────────────
    private function importarDesarquivamento(Spreadsheet $spreadsheet): void
    {
        $sheet = $this->buscarSheet($spreadsheet, 'DESARQUIVAMENTO');

        if (! $sheet) {
            Log::warning('Importação de processos: aba "DESARQUIVAMENTO" não encontrada — pulando.');
            return;
        }

        $stats = ['criados' => 0, 'erros' => 0];
        $linhasVazias = 0;
        $linha = 2;

        while ($linhasVazias < self::LINHAS_VAZIAS_PARA_PARAR) {
            $processoNumero = trim((string) $sheet->getCellByColumnAndRow(1, $linha)->getFormattedValue());

            if ($processoNumero === '') {
                $linhasVazias++;
                $linha++;
                continue;
            }

            $linhasVazias = 0;

            try {
                $normalizado = NumeroProcessoNormalizer::normalizar($processoNumero);
                $processo    = Processo::where('processo_numero_normalizado', $normalizado)->first();

                $dataCel = $this->lerCelula($sheet, 4, $linha);
                $data    = TramiteCellParser::parse($dataCel['raw'], $dataCel['formatado'])['data'] ?? null;

                Desarquivamento::updateOrCreate(
                    [
                        'processo_numero_bruto' => $processoNumero,
                        'data_solicitacao'      => $data,
                    ],
                    [
                        'processo_id' => $processo?->id,
                        'motivo'      => trim((string) $sheet->getCellByColumnAndRow(5, $linha)->getFormattedValue()) ?: null,
                    ]
                );

                $stats['criados']++;
            } catch (\Throwable $e) {
                $stats['erros']++;
                Log::error("Importação [DESARQUIVAMENTO] linha {$linha}: falha ao processar.", [
                    'processo_numero' => $processoNumero,
                    'erro'            => $e->getMessage(),
                ]);
            }

            $linha++;
        }

        $this->resumo['desarquivamento'] = $stats;
    }

    // ─────────────────────────────────────────────────────────────
    // RENOVAÇÃO_DE_ALVARÁ — agrupada por mês (linhas "JANEIRO", "FEVEREIRO"...)
    // A=data renovação | B=processo | C=nome | D=assunto | E=responsável | F=endereço | G=data entrada | H..=trâmites
    // ─────────────────────────────────────────────────────────────
    private function importarRenovacao(Spreadsheet $spreadsheet): void
    {
        $sheet = $this->buscarSheet($spreadsheet, 'RENOVAÇÃO_DE_ALVARÁ__') ?? $this->buscarSheet($spreadsheet, 'RENOVAÇÃO_DE_ALVARÁ');

        if (! $sheet) {
            Log::warning('Importação de processos: aba "RENOVAÇÃO_DE_ALVARÁ" não encontrada — pulando.');
            return;
        }

        $colunasTramite = $this->detectarColunasTramite($sheet, 1, 8);

        $stats = ['criados' => 0, 'atualizados' => 0, 'renovacoes' => 0, 'erros' => 0, 'tramites' => 0];
        $linhasVazias = 0;
        $mesAtual = null;
        $linha = 2;

        while ($linhasVazias < self::LINHAS_VAZIAS_PARA_PARAR) {
            $colA = trim((string) $sheet->getCellByColumnAndRow(1, $linha)->getFormattedValue());
            $processoNumero = trim((string) $sheet->getCellByColumnAndRow(2, $linha)->getFormattedValue());

            // Linha de cabeçalho de mês (só coluna A preenchida, com nome de mês).
            if ($processoNumero === '' && in_array(Str::of($colA)->upper()->ascii()->toString(), self::MESES, true)) {
                $mesAtual = ucfirst(Str::lower($colA));
                $linhasVazias = 0;
                $linha++;
                continue;
            }

            if ($processoNumero === '') {
                $linhasVazias++;
                $linha++;
                continue;
            }

            $linhasVazias = 0;

            try {
                DB::transaction(function () use ($sheet, $linha, $processoNumero, $colA, $mesAtual, $colunasTramite, &$stats) {
                    $assunto     = trim((string) $sheet->getCellByColumnAndRow(4, $linha)->getFormattedValue());
                    $responsavel = trim((string) $sheet->getCellByColumnAndRow(5, $linha)->getFormattedValue());

                    $dataEntradaCel = $this->lerCelula($sheet, 7, $linha);
                    $dataEntrada    = TramiteCellParser::parse($dataEntradaCel['raw'], $dataEntradaCel['formatado'])['data'] ?? null;

                    $processo = $this->criarOuAtualizarProcesso(
                        processoNumero: $processoNumero,
                        requerente: trim((string) $sheet->getCellByColumnAndRow(3, $linha)->getFormattedValue()),
                        endereco: trim((string) $sheet->getCellByColumnAndRow(6, $linha)->getFormattedValue()),
                        tipoProcessoId: TipoProcessoMatcher::match($assunto),
                        responsavelTecnicoId: ResponsavelTecnicoMatcher::matchOuCriar($responsavel),
                        dataEntrada: $dataEntrada,
                        origem: 'renovacao_alvara',
                        criados: $stats['criados'],
                        atualizados: $stats['atualizados'],
                        apenasPreencherVazios: true,
                    );

                    $dataRenovacaoCel = $this->lerCelula($sheet, 1, $linha);
                    $dataRenovacao    = TramiteCellParser::parse($dataRenovacaoCel['raw'], $dataRenovacaoCel['formatado'])['data'] ?? null;

                    RenovacaoAlvara::updateOrCreate(
                        [
                            'processo_numero_bruto' => $processoNumero,
                            'mes_referencia'         => $mesAtual,
                        ],
                        [
                            'processo_id'     => $processo->id,
                            'data_renovacao'  => $dataRenovacao,
                        ]
                    );
                    $stats['renovacoes']++;

                    $stats['tramites'] += $this->importarTramitesDaLinha($sheet, $linha, $colunasTramite, $processo, 'renovacao_alvara');
                });
            } catch (\Throwable $e) {
                $stats['erros']++;
                Log::error("Importação [RENOVAÇÃO] linha {$linha}: falha ao processar.", [
                    'processo_numero' => $processoNumero,
                    'erro'            => $e->getMessage(),
                ]);
            }

            $linha++;
        }

        $this->resumo['renovacao_alvara'] = $stats;
    }

    // ─────────────────────────────────────────────────────────────
    // HELPERS COMPARTILHADOS
    // ─────────────────────────────────────────────────────────────

    private function buscarSheet(Spreadsheet $spreadsheet, string $nome): ?Worksheet
    {
        return $spreadsheet->sheetNameExists($nome) ? $spreadsheet->getSheetByName($nome) : null;
    }

    private function lerCelula(Worksheet $sheet, int $coluna, int $linha): array
    {
        $cell = $sheet->getCellByColumnAndRow($coluna, $linha);

        return [
            'raw'       => $cell->getValue(),
            'formatado' => (string) $cell->getFormattedValue(),
        ];
    }

    /** Varre a linha de cabeçalho e retorna os índices (1-based) de colunas com "TRÂMITE" no nome. */
    private function detectarColunasTramite(Worksheet $sheet, int $linhaCabecalho, int $colunaInicial): array
    {
        $colunas = [];

        for ($col = $colunaInicial; $col <= 60; $col++) {
            $texto = Str::of((string) $sheet->getCellByColumnAndRow($col, $linhaCabecalho)->getFormattedValue())
                ->upper()->ascii()->toString();

            if (str_contains($texto, 'TRAMITE')) {
                $colunas[] = $col;
            }
        }

        return $colunas;
    }

    private function importarTramitesDaLinha(Worksheet $sheet, int $linha, array $colunasTramite, Processo $processo, string $origem): int
    {
        $importados = 0;

        foreach ($colunasTramite as $col) {
            $cel     = $this->lerCelula($sheet, $col, $linha);
            $parsead = TramiteCellParser::parse($cel['raw'], $cel['formatado']);

            if ($parsead === null) {
                continue;
            }

            $refOrigem = strtoupper(str_replace(' ', '', $origem));

            Tramite::updateOrCreate(
                ['importacao_ref' => "{$refOrigem}#L{$linha}#C{$col}"],
                [
                    'processo_id' => $processo->id,
                    'data'        => $parsead['data'],
                    'descricao'   => $parsead['descricao'],
                    'fase_id'     => $this->faseAClassificarId,
                ]
            );

            $importados++;
        }

        return $importados;
    }

    /**
     * Cria o processo se não existir (chave: número normalizado) ou atualiza um já existente.
     * Com `apenasPreencherVazios = true`, só preenche campos que ainda estão vazios no
     * registro existente — usado pelas abas "complementares" (SISOBRA, RENOVAÇÃO) para
     * não sobrescrever dados melhores já importados pela aba CONTROLE.
     */
    private function criarOuAtualizarProcesso(
        string $processoNumero,
        ?string $requerente,
        ?string $endereco,
        int $tipoProcessoId,
        ?int $responsavelTecnicoId,
        ?\Carbon\Carbon $dataEntrada,
        string $origem,
        int &$criados,
        int &$atualizados,
        bool $apenasPreencherVazios = false,
    ): Processo {
        $normalizado = NumeroProcessoNormalizer::normalizar($processoNumero);

        $processo = Processo::where('processo_numero_normalizado', $normalizado)->first();

        if (! $processo) {
            $processo = Processo::create([
                'processo_numero'             => $processoNumero,
                'processo_numero_normalizado' => $normalizado,
                'requerente'                  => $requerente ?: '(não informado)',
                'endereco'                    => $endereco ?: null,
                'tipo_processo_id'            => $tipoProcessoId,
                'responsavel_tecnico_id'      => $responsavelTecnicoId,
                'data_entrada'                => $dataEntrada,
                'fase_atual_id'               => $this->faseAClassificarId,
                'situacao'                    => Processo::SITUACAO_ABERTO,
                'origem_importacao'           => $origem,
            ]);
            $criados++;

            return $processo;
        }

        if ($apenasPreencherVazios) {
            $processo->fill(array_filter([
                'requerente'             => $processo->requerente === '(não informado)' ? $requerente : null,
                'endereco'               => $processo->endereco ?: $endereco,
                'responsavel_tecnico_id' => $processo->responsavel_tecnico_id ?: $responsavelTecnicoId,
                'data_entrada'           => $processo->data_entrada ?: $dataEntrada,
            ], fn($v) => $v !== null && $v !== ''));
        } else {
            $processo->requerente             = $requerente ?: $processo->requerente;
            $processo->endereco               = $endereco ?: $processo->endereco;
            $processo->tipo_processo_id       = $tipoProcessoId;
            $processo->responsavel_tecnico_id = $responsavelTecnicoId ?: $processo->responsavel_tecnico_id;
            $processo->data_entrada           = $dataEntrada ?: $processo->data_entrada;
        }

        if ($processo->isDirty()) {
            $processo->save();
            $atualizados++;
        }

        return $processo;
    }
}
