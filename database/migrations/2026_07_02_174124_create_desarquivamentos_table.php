<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('desarquivamentos', function (Blueprint $table) {
            $table->id();

            // Nullable: alguns registros da planilha legada podem não bater com
            // nenhum processo já cadastrado — o número original fica preservado
            // em processo_numero_bruto para curadoria manual posterior.
            $table->foreignId('processo_id')
                ->nullable()
                ->constrained('processos')
                ->nullOnDelete();
            $table->string('processo_numero_bruto', 100)->nullable();

            $table->date('data_solicitacao')->nullable();
            $table->text('motivo')->nullable();

            $table->timestamps();

            $table->index('processo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('desarquivamentos');
    }
};
