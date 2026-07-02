<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Suporte à Fase 7.2 (importação do histórico em Excel — CONTROLE_PROCESSOS.xlsx):
 * - sisobra / sisobra_data_cadastro: flag simples em processos (aba SISOBRA), em vez de
 *   tabela dedicada — a planilha só lista processo+nome+assunto, sem dado adicional.
 * - origem_importacao: rastreia de qual aba/rotina o registro veio (null = cadastrado
 *   manualmente no sistema), útil para a curadoria manual progressiva mencionada no roadmap.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            $table->boolean('sisobra')->default(false)->after('situacao');
            $table->date('sisobra_data_cadastro')->nullable()->after('sisobra');
            $table->string('origem_importacao', 50)->nullable()->after('sisobra_data_cadastro');
        });
    }

    public function down(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            $table->dropColumn(['sisobra', 'sisobra_data_cadastro', 'origem_importacao']);
        });
    }
};
