<?php

namespace App\Console\Commands;

use App\Jobs\ImportarProcessosHistoricoJob;
use Illuminate\Console\Command;

class ImportarProcessosHistorico extends Command
{
    protected $signature = 'processos:importar
        {caminho : Caminho do arquivo .xlsx (ex.: storage/app/import/CONTROLE_PROCESSOS.xlsx)}
        {--sync : Executa imediatamente em vez de enfileirar (útil para testes locais sem worker rodando)}';

    protected $description = 'Importa o histórico de processos administrativos da planilha legada (Fase 7.2)';

    public function handle(): int
    {
        $caminho = base_path($this->argument('caminho'));

        if (! is_file($caminho)) {
            // Tenta como caminho absoluto, caso o usuário já tenha passado assim.
            $caminho = $this->argument('caminho');
        }

        if (! is_file($caminho)) {
            $this->error("Arquivo não encontrado: {$caminho}");

            return self::FAILURE;
        }

        if ($this->option('sync')) {
            $this->info('Executando importação de forma síncrona (isso pode levar alguns minutos)...');

            $job = new ImportarProcessosHistoricoJob($caminho);
            $job->handle();

            $this->newLine();
            $this->info('Importação concluída. Resumo por aba:');

            foreach ($job->resumo() as $origem => $stats) {
                $this->line("  {$origem}: " . collect($stats)->map(fn($v, $k) => "{$k}={$v}")->implode(', '));
            }

            $this->newLine();
            $this->comment('Log completo em storage/logs/laravel.log.');

            return self::SUCCESS;
        }

        ImportarProcessosHistoricoJob::dispatch($caminho);

        $this->info('Importação enfileirada com sucesso.');
        $this->comment('Rode "php artisan queue:work" para processá-la, ou use --sync para rodar agora mesmo.');

        return self::SUCCESS;
    }
}
