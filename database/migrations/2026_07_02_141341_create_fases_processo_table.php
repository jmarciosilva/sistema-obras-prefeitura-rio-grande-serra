<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * ⚠️ Vocabulário de fases ainda NÃO é definitivo — ver roadmap-modulo-processos-administrativos.md,
 * seção 4 (Fase 7.1) e seção 5 (Riscos). Precisa ser validado com as técnicas do
 * Departamento de Obras Particulares antes de considerar este seed como fechado.
 * Por isso "fase" é uma tabela de domínio editável, e não um enum fixo no banco.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fases_processo', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 150);
            $table->string('cor', 7)->default('#64748b'); // hex, ex: #64748b — usada nos badges das telas
            $table->integer('ordem')->default(0);
            $table->timestamps();

            $table->unique('nome');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fases_processo');
    }
};
