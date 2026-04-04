<?php

namespace Database\Seeders;

use App\Models\Empresa;
use Illuminate\Database\Seeder;

class EmpresaSeeder extends Seeder
{
    public function run(): void
    {
        $empresas = [
            [
                'razao_social'      => 'Construtora Exemplo LTDA',
                'nome_fantasia'     => 'Exemplo Construções',
                'cnpj'              => '12.345.678/0001-99',
                'telefone'          => '(11) 91234-5678',
                'email'             => 'contato@exemploconstrucoes.com.br',
                'responsavel'       => 'João da Silva',
                'endereco_completo' => 'Rua das Obras, 100 - Centro - Rio Grande da Serra/SP',
            ],
            [
                'razao_social'      => 'Pavimentadora Sul EIRELI',
                'nome_fantasia'     => 'PavSul',
                'cnpj'              => '98.765.432/0001-11',
                'telefone'          => '(11) 97654-3210',
                'email'             => 'obras@pavsul.com.br',
                'responsavel'       => 'Maria Oliveira',
                'endereco_completo' => 'Av. Principal, 500 - Bairro Industrial - Mauá/SP',
            ],
        ];

        foreach ($empresas as $dados) {
            Empresa::updateOrCreate(['cnpj' => $dados['cnpj']], $dados);
        }

        $this->command->info('✅ Empresas de exemplo inseridas.');
    }
}
