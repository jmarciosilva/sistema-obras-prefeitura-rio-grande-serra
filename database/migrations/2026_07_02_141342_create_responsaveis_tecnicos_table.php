<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('responsaveis_tecnicos', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 200);
            $table->string('registro', 50)->nullable(); // CREA/CAU
            $table->string('telefone', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('responsaveis_tecnicos');
    }
};
