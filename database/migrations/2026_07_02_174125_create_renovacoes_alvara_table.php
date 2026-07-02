<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('renovacoes_alvara', function (Blueprint $table) {
            $table->id();

            // Nullable pelo mesmo motivo de desarquivamentos.processo_id — ver comentário lá.
            $table->foreignId('processo_id')
                ->nullable()
                ->constrained('processos')
                ->nullOnDelete();
            $table->string('processo_numero_bruto', 100)->nullable();

            $table->date('data_renovacao')->nullable();
            $table->string('mes_referencia', 20)->nullable();

            $table->timestamps();

            $table->index('processo_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('renovacoes_alvara');
    }
};
