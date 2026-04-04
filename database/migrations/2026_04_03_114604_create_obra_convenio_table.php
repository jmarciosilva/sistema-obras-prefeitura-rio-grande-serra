<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Cria a tabela pivot obra_convenio (N:M).
 *
 * Uma obra pode ser financiada por vários convênios,
 * e um convênio pode financiar várias obras.
 * Chave primária composta: (obra_id, convenio_id).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obra_convenio', function (Blueprint $table) {
            // Ao excluir uma obra ou convênio, o vínculo é removido automaticamente
            $table->foreignId('obra_id')->constrained('obras')->cascadeOnDelete();
            $table->foreignId('convenio_id')->constrained('convenios')->cascadeOnDelete();

            // Chave primária composta — evita vínculos duplicados
            $table->primary(['obra_id', 'convenio_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obra_convenio');
    }
};
