<?php

namespace Database\Seeders;

use App\Models\TipoProcesso;
use Illuminate\Database\Seeder;

/**
 * Os 12 serviços principais descritos no documento formal da Secretaria
 * (Serviços001.docx — ver roadmap-modulo-processos-administrativos.md, seção 1.1).
 *
 * Não inclui SISOBRA, Água e Luz, Renovação de Alvará ou Desarquivamento — esses
 * fluxos aparecem só na planilha (uso real) e entram como sub-módulos na Fase 7.4.
 */
class TipoProcessoSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            ['nome' => 'Alvará de Construção',                       'sigla' => 'AC',  'ordem' => 1],
            ['nome' => 'Alvará de Reforma',                          'sigla' => 'AR',  'ordem' => 2],
            ['nome' => 'Alvará de Demolição',                        'sigla' => 'AD',  'ordem' => 3],
            ['nome' => 'Alvará de Movimentação de Terra',            'sigla' => 'AMT', 'ordem' => 4],
            ['nome' => 'Alvará de Regularização',                    'sigla' => 'ARG', 'ordem' => 5],
            ['nome' => 'Habite-se (Conclusão de Obra)',               'sigla' => 'HB',  'ordem' => 6],
            ['nome' => 'Certidão de Uso e Ocupação do Solo',          'sigla' => 'CUOS','ordem' => 7],
            ['nome' => 'Diretrizes Urbanísticas',                    'sigla' => 'DU',  'ordem' => 8],
            ['nome' => 'Ofício de Ligação de Água/Energia',          'sigla' => 'OLAE','ordem' => 9],
            ['nome' => 'Alvará de Desdobro/Unificação/Desmembramento', 'sigla' => 'ADUD', 'ordem' => 10],
            ['nome' => 'Muro de Contenção',                          'sigla' => 'MC',  'ordem' => 11],
            ['nome' => 'Manutenção de Iluminação Pública',           'sigla' => 'MIP', 'ordem' => 12],
        ];

        foreach ($tipos as $t) {
            TipoProcesso::updateOrCreate(['nome' => $t['nome']], $t);
        }

        $this->command->info('✅ Tipos de processo inseridos.');
    }
}
