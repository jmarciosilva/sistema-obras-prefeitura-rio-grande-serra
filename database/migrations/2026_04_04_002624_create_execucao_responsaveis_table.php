<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Responsáveis por uma medição de obra.
 *
 * Uma medição pode ter múltiplos responsáveis com papéis distintos:
 * engenheiro, fiscal, supervisor.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('execucao_responsaveis', function (Blueprint $table) {
            $table->id();

            $table->foreignId('execucao_obra_id')
                ->constrained('execucao_obras')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->enum('papel', ['engenheiro', 'fiscal', 'supervisor'])
                ->default('engenheiro');

            // Garante que o mesmo usuário não apareça duas vezes na mesma medição
            $table->unique(['execucao_obra_id', 'user_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('execucao_responsaveis');
    }
};
