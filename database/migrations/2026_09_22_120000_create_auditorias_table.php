<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Auditoria (histórico de atividades) — somente ADITIVA:
 * nenhuma tabela existente é alterada.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditorias', function (Blueprint $table) {
            $table->id();

            // Usuário excluído depois não apaga o histórico
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('acao', 30);                 // criou | alterou | excluiu
            $table->string('auditable_type');           // App\Models\Obra ...
            $table->unsignedBigInteger('auditable_id'); // sem FK: o registro pode ter sido excluído
            $table->string('descricao')->nullable();

            $table->json('dados_antes')->nullable();
            $table->json('dados_depois')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['auditable_type', 'auditable_id']);
            $table->index('acao');
            $table->index('created_at'); // listagem "mais recentes primeiro"
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditorias');
    }
};
