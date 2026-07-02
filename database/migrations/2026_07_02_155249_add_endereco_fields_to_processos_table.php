<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Campos estruturados de endereço, preenchidos automaticamente via busca de CEP
 * (API pública ViaCEP) no formulário. `endereco` continua sendo o logradouro
 * (mantém o nome de coluna já usado pelo restante do sistema); número e
 * complemento (apto/bloco/condomínio) são sempre preenchidos manualmente,
 * pois a API de CEP não tem como saber esses dados.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            $table->string('cep', 9)->nullable()->after('endereco');
            $table->string('numero', 20)->nullable()->after('cep');
            $table->string('complemento', 150)->nullable()->after('numero');
            $table->string('bairro', 150)->nullable()->after('complemento');
            $table->string('cidade', 150)->nullable()->after('bairro');
            $table->string('uf', 2)->nullable()->after('cidade');
        });
    }

    public function down(): void
    {
        Schema::table('processos', function (Blueprint $table) {
            $table->dropColumn(['cep', 'numero', 'complemento', 'bairro', 'cidade', 'uf']);
        });
    }
};
