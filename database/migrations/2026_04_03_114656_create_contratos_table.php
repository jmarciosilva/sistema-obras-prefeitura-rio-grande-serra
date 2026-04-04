<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Problemas corrigidos vs migration original:
 *  - "processo_adm_licitacao" → "processo_licitacao"  (ContratoController valida 'processo_licitacao')
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contratos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('obra_id')
                ->constrained('obras')
                ->cascadeOnDelete();

            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->restrictOnDelete();

            $table->string('processo_licitacao', 100)->nullable();   // era processo_adm_licitacao
            $table->string('numero_contrato_ano', 60)->nullable();
            $table->date('data_assinatura')->nullable();
            $table->date('ordem_inicio')->nullable();
            $table->date('vigencia_contrato')->nullable();
            $table->decimal('valor_contrato', 18, 2)->nullable();

            $table->index('obra_id');
            $table->index('vigencia_contrato');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contratos');
    }
};
