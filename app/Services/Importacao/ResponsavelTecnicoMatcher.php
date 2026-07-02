<?php

namespace App\Services\Importacao;

use App\Models\ResponsavelTecnico;
use Illuminate\Support\Str;

/**
 * Casa (ou cria) um Responsável Técnico a partir do texto livre da planilha legada.
 *
 * O roadmap sinaliza explicitamente esse problema (seção 5, "Responsáveis técnicos
 * duplicados"): a mesma pessoa aparece com grafias diferentes, ex.: "PRISCILA DE
 * JESUS GUERRA ANDRÉ" / "PRISCILA DE JESUS A. GUERRA" / "PRISCILA DE JESUS G. ANDRÉ".
 * Usa correspondência aproximada (similar_text) com limiar calibrado nos dados reais
 * da planilha — casos ambíguos (< limiar) viram registros separados, para revisão
 * manual em vez de mesclar pessoas potencialmente diferentes.
 */
class ResponsavelTecnicoMatcher
{
    /** Similaridade mínima (0-100) para considerar duas grafias a mesma pessoa. */
    private const LIMIAR_SIMILARIDADE = 82.0;

    /** @var array<int,string>|null id => nome normalizado (cache em memória durante a importação) */
    private static ?array $cache = null;

    public static function matchOuCriar(?string $nomeBruto): ?int
    {
        $nomeLimpo = self::limparNome((string) $nomeBruto);

        if ($nomeLimpo === '') {
            return null;
        }

        self::carregarCache();

        $normalizado = self::normalizar($nomeLimpo);

        $melhorId  = null;
        $melhorPct = 0.0;

        foreach (self::$cache as $id => $nomeExistente) {
            if ($nomeExistente === $normalizado) {
                return $id;
            }

            similar_text($normalizado, $nomeExistente, $pct);

            if ($pct > $melhorPct) {
                $melhorPct = $pct;
                $melhorId  = $id;
            }
        }

        if ($melhorId !== null && $melhorPct >= self::LIMIAR_SIMILARIDADE) {
            return $melhorId;
        }

        $responsavel = ResponsavelTecnico::create(['nome' => $nomeLimpo]);
        self::$cache[$responsavel->id] = $normalizado;

        return $responsavel->id;
    }

    /** Remove títulos (ENG./ARQ.) e placeholders comuns na planilha (ex.: "- - - - -"). */
    private static function limparNome(string $nome): string
    {
        $nome = trim($nome);
        $nome = preg_replace('/^(ENG\.?|ARQ\.?)\s+/i', '', $nome);
        $nome = trim(preg_replace('/\s+/', ' ', $nome));

        // Placeholders tipo "- - - - - - -" ou nomes só com pontuação/traço.
        if ($nome === '' || preg_match('/^[\-\.\s]*$/', $nome)) {
            return '';
        }

        return $nome;
    }

    private static function normalizar(string $nome): string
    {
        return Str::of($nome)->upper()->ascii()->toString();
    }

    private static function carregarCache(): void
    {
        if (self::$cache !== null) {
            return;
        }

        self::$cache = ResponsavelTecnico::pluck('nome', 'id')
            ->map(fn($nome) => self::normalizar($nome))
            ->all();
    }

    /** Limpa o cache em memória — usado entre execuções de teste/reprocessamento. */
    public static function resetCache(): void
    {
        self::$cache = null;
    }
}
