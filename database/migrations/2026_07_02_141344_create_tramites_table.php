<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * setor_destino e tipo_evento ficam como string livre (não enum de banco), porque a lista
 * definitiva de setores/eventos ainda não foi fechada com a Secretaria (ver roadmap,
 * seção 5 — "Vocabulário de fases ainda não fechado" e seção 6, item 4). As sugestões de
 * valores ficam documentadas como constantes no Model Tramite.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tramites', function (Blueprint $table) {
            $table->id();

            $table->foreignId('processo_id')
                ->constrained('processos')
                ->cascadeOnDelete();

            $table->date('data');
            $table->text('descricao');

            $table->foreignId('fase_id')
                ->nullable()
                ->constrained('fases_processo')
                ->nullOnDelete();

            $table->string('setor_destino', 100)->nullable();
            $table->string('tipo_evento', 100)->nullable();

            $table->timestamps();

            $table->index('processo_id');
            $table->index('data');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tramites');
    }
};
