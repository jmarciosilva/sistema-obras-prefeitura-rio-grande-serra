<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // 1. Dados de suporte (sem dependências)
            StatusObraSeeder::class,
            CategoriaConvenioSeeder::class,
            OrgaoFinanciadorSeeder::class,

            // 2. Usuários
            UserSeeder::class,

            // 3. Dados de negócio (dependem dos acima)
            EmpresaSeeder::class,
        ]);
    }
}
