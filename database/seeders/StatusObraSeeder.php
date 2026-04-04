<?php
// ─────────────────────────────────────────────────────────────────────────────
// StatusObraSeeder.php
// ─────────────────────────────────────────────────────────────────────────────
namespace Database\Seeders;

use App\Models\StatusObra;
use Illuminate\Database\Seeder;

class StatusObraSeeder extends Seeder
{
    public function run(): void
    {
        $statuses = [
            ['nome' => 'Em Planejamento', 'cor' => '#6c757d', 'ordem' => 1],
            ['nome' => 'Licitação',       'cor' => '#fd7e14', 'ordem' => 2],
            ['nome' => 'Em Execução',     'cor' => '#0d6efd', 'ordem' => 3],
            ['nome' => 'Paralisada',      'cor' => '#dc3545', 'ordem' => 4],
            ['nome' => 'Concluída',       'cor' => '#198754', 'ordem' => 5],
            ['nome' => 'Cancelada',       'cor' => '#343a40', 'ordem' => 6],
        ];

        foreach ($statuses as $s) {
            StatusObra::updateOrCreate(['nome' => $s['nome']], $s);
        }

        $this->command->info('✅ Status de obras inseridos.');
    }
}
