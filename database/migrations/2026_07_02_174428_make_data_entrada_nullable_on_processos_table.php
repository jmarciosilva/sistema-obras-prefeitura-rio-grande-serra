<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * ~12% dos registros da planilha legada não têm "DATA ENTRADA EM OBRAS" preenchida
 * (célula vazia ou texto não interpretável como data). O cadastro manual (Fase 7.1)
 * continua exigindo essa data via validação no ProcessoController — só a coluna do
 * banco precisa aceitar nulo para não inventar datas falsas em dados importados.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            $table->date('data_entrada')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            $table->date('data_entrada')->nullable(false)->change();
        });
    }
};
