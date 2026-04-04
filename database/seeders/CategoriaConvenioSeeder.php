<?php

namespace Database\Seeders;

use App\Models\CategoriaConvenio;
use Illuminate\Database\Seeder;

class CategoriaConvenioSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            'Pavimentação e Infraestrutura Viária',
            'Saneamento Básico',
            'Habitação',
            'Saúde',
            'Educação',
            'Esporte e Lazer',
            'Cultura',
            'Meio Ambiente',
            'Iluminação Pública',
            'Outros',
        ];

        foreach ($categorias as $nome) {
            CategoriaConvenio::updateOrCreate(['nome' => $nome]);
        }

        $this->command->info('✅ Categorias de convênio inseridas.');
    }
}
