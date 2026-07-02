<?php

namespace Database\Seeders;

use App\Models\FaseProcesso;
use Illuminate\Database\Seeder;

/**
 * ⚠️ ATENÇÃO — vocabulário de fases NÃO é definitivo.
 *
 * Esta é a "sugestão inicial" de fases citada no roadmap
 * (roadmap-modulo-processos-administrativos.md, seção 4 — Fase 7.1), montada a partir
 * do relato em áudio da cliente da Secretaria de Obras. Antes de considerar este
 * vocabulário fechado, é preciso agendar a chamada de vídeo com as técnicas do
 * Departamento de Obras Particulares (ver seção 6, item 1 do roadmap) para validar
 * nomes, ordem e granularidade das fases.
 *
 * Como fases_processo é uma tabela de domínio (não um enum de banco), renomear,
 * reordenar ou adicionar fases depois dessa validação é uma operação simples de dado,
 * sem necessidade de nova migration.
 */
class FaseProcessoSeeder extends Seeder
{
    public function run(): void
    {
        $fases = [
            ['nome' => 'Protocolado',                                  'cor' => '#64748b', 'ordem' => 1],
            ['nome' => 'Recebido em Obras',                            'cor' => '#94a3b8', 'ordem' => 2],
            ['nome' => 'Em Primeira Análise',                          'cor' => '#0d6efd', 'ordem' => 3],
            ['nome' => 'Notificado — Aguardando Devolutiva',           'cor' => '#f59e0b', 'ordem' => 4],
            ['nome' => 'Em Reanálise',                                 'cor' => '#0ea5e9', 'ordem' => 5],
            ['nome' => 'Emitido / Encaminhado ao CTM',                 'cor' => '#8b5cf6', 'ordem' => 6],
            ['nome' => 'Aguardando Pagamento',                         'cor' => '#fd7e14', 'ordem' => 7],
            ['nome' => 'Retirada de Documentação',                     'cor' => '#6366f1', 'ordem' => 8],
            ['nome' => 'Arquivado',                                    'cor' => '#198754', 'ordem' => 9],
        ];

        foreach ($fases as $f) {
            FaseProcesso::updateOrCreate(['nome' => $f['nome']], $f);
        }

        $this->command->info('✅ Fases de processo inseridas (vocabulário inicial — validar com as técnicas).');
    }
}
