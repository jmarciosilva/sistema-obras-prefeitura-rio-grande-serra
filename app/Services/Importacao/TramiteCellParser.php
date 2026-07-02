<?php

namespace App\Services\Importacao;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Interpreta uma célula de trâmite da planilha legada (CONTROLE_PROCESSOS.xlsx).
 *
 * A mesma coluna mistura, linha a linha:
 *  - números seriais do Excel puros (ex.: 44106) quando alguém digitou só uma data;
 *  - texto livre com uma data embutida em algum ponto (ex.: "TEC. PEDIU PRAZO 13/10/20");
 *  - texto livre sem nenhuma data reconhecível (ex.: "AGUARDA EM DESMEMBRAMENTOS...");
 *  - célula vazia.
 *
 * O texto original NUNCA é alterado/recortado (vira a `descricao` do trâmite tal como
 * está na planilha) — só extraímos uma data quando é possível, com fallback para nulo
 * ("data não informada") quando não é, conforme roadmap seção 4 (Fase 7.2).
 */
class TramiteCellParser
{
    /**
     * @return array{data: ?Carbon, descricao: string}|null null quando a célula está vazia.
     */
    public static function parse(mixed $rawValue, string $formattedValue): ?array
    {
        $formattedValue = trim($formattedValue);

        if ($rawValue === null || $rawValue === '') {
            return null;
        }

        // Célula é puramente um número serial do Excel (ex.: 44106) — sem texto adicional.
        if (is_numeric($rawValue) && self::pareceSerialDeData((float) $rawValue)) {
            $data = self::converterSerialExcel((float) $rawValue);

            return [
                'data'      => $data,
                'descricao' => $formattedValue !== '' ? $formattedValue : (string) $rawValue,
            ];
        }

        $texto = is_string($rawValue) ? trim($rawValue) : $formattedValue;

        if ($texto === '') {
            return null;
        }

        return [
            'data'      => self::extrairDataDoTexto($texto),
            'descricao' => $texto,
        ];
    }

    private static function pareceSerialDeData(float $valor): bool
    {
        // Faixa aproximada de datas plausíveis para o histórico (anos ~1988 a ~2040).
        return $valor >= 32000 && $valor <= 51500;
    }

    private static function converterSerialExcel(float $serial): ?Carbon
    {
        try {
            return Carbon::instance(ExcelDate::excelToDateTimeObject($serial));
        } catch (\Throwable) {
            return null;
        }
    }

    /** Procura o primeiro padrão dd/mm/aa(aa) em qualquer posição do texto — convenção brasileira. */
    private static function extrairDataDoTexto(string $texto): ?Carbon
    {
        if (! preg_match('/\b(\d{1,2})\/(\d{1,2})\/(\d{2,4})\b/', $texto, $m)) {
            return null;
        }

        [$_, $dia, $mes, $ano] = $m;
        $dia = (int) $dia;
        $mes = (int) $mes;
        $ano = (int) $ano;

        if (strlen((string) $ano) === 2) {
            $ano += $ano <= 40 ? 2000 : 1900;
        }

        if (! checkdate($mes, $dia, $ano)) {
            return null;
        }

        try {
            return Carbon::createFromDate($ano, $mes, $dia)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }
}
