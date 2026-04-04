<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Cria a tabela de órgãos financiadores.
 *
 * Registra os órgãos que concedem convênios para a prefeitura.
 * Exemplos: CAIXA Econômica Federal, Governo do Estado de SP.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orgaos_financiadores', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150)->unique();   // era nome_orgao
            $table->string('sigla', 30)->nullable();
            $table->enum('esfera', ['federal', 'estadual', 'municipal'])->default('federal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orgaos_financiadores');
    }
};
