<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Chave natural (ex.: "CONTROLE#linha5#colG") usada pelo importador de Excel para
 * ser idempotente: reprocessar a mesma planilha atualiza o trâmite já importado
 * em vez de duplicá-lo. Trâmites lançados manualmente no sistema deixam esse campo nulo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tramites', function (Blueprint $table) {
            $table->string('importacao_ref', 100)->nullable()->unique()->after('tipo_evento');
        });
    }

    public function down(): void
    {
        Schema::table('tramites', function (Blueprint $table) {
            $table->dropColumn('importacao_ref');
        });
    }
};
