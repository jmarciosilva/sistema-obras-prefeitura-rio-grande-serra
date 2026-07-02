<?php

namespace App\Services\Importacao;

/**
 * Normaliza o número de processo para permitir busca/deduplicação, tanto no
 * cadastro manual (ProcessoController) quanto na importação em massa (Fase 7.2).
 *
 * Formatos reais observados na planilha legada: "1831/2019-5", "2107/2023 Vol. 1",
 * "1045/92 AC 1130/1992", "117/2016-6". A normalização remove tudo que não for
 * letra/número e coloca em caixa alta — não tenta separar volume/AC do número base
 * (isso ficaria para uma futura relação `processo_relacionados`, fora do escopo atual).
 */
class NumeroProcessoNormalizer
{
    public static function normalizar(string $numero): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $numero));
    }
}
