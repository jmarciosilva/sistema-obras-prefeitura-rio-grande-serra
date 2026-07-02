<?php

namespace App\Services\Importacao;

use App\Models\TipoProcesso;
use Illuminate\Support\Str;

/**
 * Casa o texto livre da coluna ASSUNTO da planilha legada com um dos 12 serviços
 * formais (TipoProcessoSeeder). A planilha real tem mais de 500 variações distintas
 * de texto para "assunto" (abreviações, erros de digitação, sinônimos) — por
 * design, esse matcher cobre só os padrões mais frequentes/óbvios por palavra-chave.
 *
 * Conforme o roadmap (Fase 7.2): "Não tentar categorizar 100% do texto livre
 * histórico automaticamente — permitir curadoria manual progressiva". Tudo que não
 * bate com nenhuma regra cai em "Outros / A Classificar" para revisão posterior.
 */
class TipoProcessoMatcher
{
    /** Regras em ordem de prioridade — a primeira que bater vence. */
    private const REGRAS = [
        'Alvará de Demolição'                          => ['DEMOLICAO', 'DEMOLIÇÃO'],
        'Alvará de Reforma'                             => ['REFORMA'],
        'Alvará de Movimentação de Terra'               => ['MOVIMENTACAO DE TERRA', 'MOVIMENTAÇÃO DE TERRA', 'TERRAPLANAGEM'],
        'Alvará de Desdobro/Unificação/Desmembramento'  => ['DESDOBRO', 'DESMEMBRA', 'UNIFICACAO DE LOTE', 'UNIFICAÇÃO DE LOTE'],
        'Alvará de Regularização'                       => ['REGULARIZACAO', 'REGULARIZAÇÃO'],
        'Habite-se (Conclusão de Obra)'                  => ['HABITE-SE', 'HABITE SE', 'CONCLUSAO DE OBRA', 'CONCLUSÃO DE OBRA'],
        'Diretrizes Urbanísticas'                       => ['DIRETRIZES'],
        'Certidão de Uso e Ocupação do Solo'             => ['USO DO SOLO', 'USO DE SOLO', 'USO E OCUPACAO', 'USO E OCUPAÇÃO'],
        'Ofício de Ligação de Água/Energia'             => ['LIGACAO DE AGUA', 'LIGAÇÃO DE ÁGUA', 'AGUA E LUZ', 'ÁGUA E LUZ', 'LIGACAO DE LUZ', 'LIGAÇÃO DE LUZ', 'RELOGIO', 'RELÓGIO'],
        'Muro de Contenção'                             => ['MURO DE CONTENCAO', 'MURO DE CONTENÇÃO'],
        'Manutenção de Iluminação Pública'              => ['ILUMINACAO', 'ILUMINAÇÃO'],
        // "Alvará de Construção" por último — "PLANTA"/"CONSTRUÇÃO" aparecem em muitos
        // assuntos que já foram tratados por regras mais específicas acima.
        'Alvará de Construção'                          => ['APROVACAO DE PLANTA', 'APROVAÇÃO DE PLANTA', 'AP. DE PLANTA', 'AP DE PLANTA', 'CONSTRUCAO', 'CONSTRUÇÃO'],
    ];

    /** @var array<string,int>|null cache em memória (nome do tipo => id) */
    private static ?array $idsPorNome = null;

    public static function match(?string $assunto): int
    {
        $assuntoNormalizado = self::normalizar((string) $assunto);

        foreach (self::REGRAS as $nomeTipo => $palavrasChave) {
            foreach ($palavrasChave as $palavra) {
                if (str_contains($assuntoNormalizado, self::normalizar($palavra))) {
                    return self::idPorNome($nomeTipo);
                }
            }
        }

        return self::idPorNome('Outros / A Classificar');
    }

    private static function normalizar(string $texto): string
    {
        return Str::of($texto)->upper()->ascii()->toString();
    }

    private static function idPorNome(string $nome): int
    {
        if (self::$idsPorNome === null) {
            self::$idsPorNome = TipoProcesso::pluck('id', 'nome')->all();
        }

        return self::$idsPorNome[$nome]
            ?? throw new \RuntimeException("Tipo de processo \"{$nome}\" não encontrado — rode o TipoProcessoSeeder.");
    }
}
