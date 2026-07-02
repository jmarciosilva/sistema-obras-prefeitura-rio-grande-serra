<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Trâmites importados do histórico legado frequentemente têm texto livre sem
 * nenhuma data reconhecível (planilha real: datas corrompidas, abreviadas ou
 * simplesmente ausentes). O roadmap prevê explicitamente esse fallback
 * ("data não informada") — a coluna do banco precisa aceitar nulo para isso.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tramites', function (Blueprint $table) {
            $table->date('data')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('tramites', function (Blueprint $table) {
            $table->date('data')->nullable(false)->change();
        });
    }
};
