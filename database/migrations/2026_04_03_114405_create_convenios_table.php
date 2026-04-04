<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Cria a tabela de convênios.
 *
 * Cada convênio é o instrumento jurídico entre a prefeitura e o órgão
 * financiador que viabiliza a execução de uma ou mais obras.
 * O relacionamento com obras é N:M (via tabela obra_convenio).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convenios', function (Blueprint $table) {
            $table->id();

            // FK → categoria_convenios (plural — nome correto da tabela)
            $table->foreignId('categoria_convenio_id')
                ->nullable()
                ->constrained('categoria_convenios')
                ->nullOnDelete();

            // FK → orgaos_financiadores
            $table->foreignId('orgao_financiador_id')
                ->nullable()
                ->constrained('orgaos_financiadores')
                ->nullOnDelete();

            // FK → demandas_propostas
            $table->foreignId('demanda_proposta_id')
                ->nullable()
                ->constrained('demandas_propostas')
                ->nullOnDelete();

            $table->text('descricao')->nullable();
            $table->string('processo_pref_concedente', 100)->nullable();
            $table->string('numero_convenio_ano', 60)->nullable();
            $table->decimal('valor_repasse_contrapartida', 18, 2)->nullable();
            $table->date('assinatura')->nullable();
            $table->date('vigencia')->nullable();
            $table->string('cadastro_prescon_num', 60)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convenios');
    }
};
