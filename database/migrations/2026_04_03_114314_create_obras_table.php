<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Problemas corrigidos vs migration original:
 *  - "nome_obra"          → "descricao"           (ObraController valida 'descricao')
 *  - "demanda_proposta_num" → removido             (substituído por FK demanda_proposta_id)
 *  - "bairro_localizacao" → "endereco"             (ObraController valida 'endereco')
 *  - valor_medido / saldo_contratual / percentual  → removidos (ficam em execucao_obras)
 *  - adicionado "demanda_proposta_id" (FK nullable) (ObraController valida esse campo)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obras', function (Blueprint $table) {
            $table->id();
            $table->string('descricao', 500);                    // era nome_obra
            $table->string('endereco', 300)->nullable();         // era bairro_localizacao
            $table->string('processo_execucao', 100)->nullable();
            $table->text('observacoes')->nullable();

            $table->foreignId('status_obra_id')
                ->constrained('status_obras')
                ->restrictOnDelete();

            $table->foreignId('demanda_proposta_id')             // era apenas string
                ->nullable()
                ->constrained('demandas_propostas')
                ->nullOnDelete();

            $table->index('status_obra_id');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obras');
    }
};
