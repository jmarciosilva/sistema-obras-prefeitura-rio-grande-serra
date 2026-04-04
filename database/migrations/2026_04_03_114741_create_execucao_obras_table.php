<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Problemas corrigidos vs migration original:
 *  - "processo_execucao"   → removido   (não existe no ExecucaoObraController)
 *  - "observacoes"         → "observacao" (singular — ExecucaoObraController valida 'observacao')
 *  - percentual_executado  → nullable   (controller não o exige)
 *  - adicionado saldo_contratual nullable (ExecucaoObraController valida esse campo)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('execucao_obras', function (Blueprint $table) {
            $table->id();

            $table->foreignId('contrato_id')
                ->constrained('contratos')
                ->cascadeOnDelete();

            $table->date('data_medicao');
            $table->decimal('valor_medido', 15, 2);
            $table->decimal('saldo_contratual', 15, 2)->nullable();      // adicionado
            $table->decimal('percentual_executado', 5, 2)->nullable();   // era required
            $table->text('observacao')->nullable();                       // era "observacoes"

            $table->index(['contrato_id', 'data_medicao']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('execucao_obras');
    }
};
