<?php

namespace Database\Seeders;

use App\Models\OrgaoFinanciador;
use Illuminate\Database\Seeder;

class OrgaoFinanciadorSeeder extends Seeder
{
    public function run(): void
    {
        $orgaos = [
            // Federais
            ['nome' => 'Ministério das Cidades',                        'sigla' => 'MCid',   'esfera' => 'federal'],
            ['nome' => 'Ministério da Saúde',                           'sigla' => 'MS',     'esfera' => 'federal'],
            ['nome' => 'Ministério da Educação',                        'sigla' => 'MEC',    'esfera' => 'federal'],
            ['nome' => 'Ministério do Desenvolvimento Regional',        'sigla' => 'MDR',    'esfera' => 'federal'],
            ['nome' => 'Caixa Econômica Federal',                       'sigla' => 'CEF',    'esfera' => 'federal'],
            ['nome' => 'Banco Nacional de Desenvolvimento Econômico',   'sigla' => 'BNDES',  'esfera' => 'federal'],
            ['nome' => 'Funasa',                                        'sigla' => 'FUNASA', 'esfera' => 'federal'],

            // Estaduais (SP)
            ['nome' => 'Secretaria de Infraestrutura e Meio Ambiente',  'sigla' => 'SIMA',   'esfera' => 'estadual'],
            ['nome' => 'Secretaria de Desenvolvimento Regional',        'sigla' => 'SDR',    'esfera' => 'estadual'],
            ['nome' => 'SABESP',                                        'sigla' => 'SABESP', 'esfera' => 'estadual'],
            ['nome' => 'Departamento de Estradas de Rodagem',           'sigla' => 'DER',    'esfera' => 'estadual'],

            // Municipal
            ['nome' => 'Recursos Próprios do Município',                'sigla' => null,     'esfera' => 'municipal'],
        ];

        foreach ($orgaos as $orgao) {
            OrgaoFinanciador::updateOrCreate(
                ['nome' => $orgao['nome']],
                $orgao
            );
        }

        $this->command->info('✅ Órgãos financiadores inseridos.');
    }
}
