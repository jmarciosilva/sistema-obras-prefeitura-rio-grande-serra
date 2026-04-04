<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Problemas corrigidos vs migration original:
 *  - "titulo"         → "descricao"      (Model usa $this->descricao)
 *  - "status_demanda" → "situacao"       (Model usa situacao + constantes)
 *  - valores do enum  → minúsculos       (Model: 'pendente','aprovada','rejeitada','em_andamento')
 *  - "justificativa"  → removida         (não existe no Model nem nos controllers)
 *  - adicionado "origem"                 (Model tem campo origem obrigatório)
 *  - data_solicitacao → nullable         (Model não o exige)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demandas_propostas', function (Blueprint $table) {
            $table->id();
            $table->string('numero_demanda', 100)->unique();
            $table->string('descricao');                         // era "titulo"
            $table->string('origem', 30)->default('outros');     // novo — secretaria|vereador|estado|federal|outros
            $table->string('solicitante')->nullable();
            $table->date('data_solicitacao')->nullable();        // era required
            $table->string('situacao', 30)->default('pendente'); // era status_demanda com enum maiúsculo
            $table->text('observacoes')->nullable();             // era "justificativa"+"observacoes"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demandas_propostas');
    }
};
