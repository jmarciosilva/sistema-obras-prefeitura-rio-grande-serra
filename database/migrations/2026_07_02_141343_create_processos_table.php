<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('processos', function (Blueprint $table) {
            $table->id();

            $table->string('processo_numero', 100);
            $table->string('processo_numero_normalizado', 100)->nullable();
            $table->string('requerente', 300);
            $table->string('endereco', 300)->nullable();

            $table->foreignId('tipo_processo_id')
                ->constrained('tipos_processo')
                ->restrictOnDelete();

            $table->foreignId('responsavel_tecnico_id')
                ->nullable()
                ->constrained('responsaveis_tecnicos')
                ->nullOnDelete();

            $table->date('data_entrada');

            $table->foreignId('fase_atual_id')
                ->constrained('fases_processo')
                ->restrictOnDelete();

            $table->string('setor_atual', 100)->nullable();
            $table->string('caixa_atual', 100)->nullable();

            // Preenchido/atualizado quando a técnica registra um trâmite indicando pendência
            // (ex.: "Notificado — aguardando devolutiva do técnico"). É a resposta direta
            // ao pedido do Secretário: "se estiver parado, por qual motivo".
            $table->text('motivo_pendencia')->nullable();

            $table->enum('situacao', ['aberto', 'arquivado'])->default('aberto');

            $table->timestamps();

            $table->index('processo_numero');
            $table->index('processo_numero_normalizado');
            $table->index('tipo_processo_id');
            $table->index('fase_atual_id');
            $table->index('data_entrada');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processos');
    }
};
