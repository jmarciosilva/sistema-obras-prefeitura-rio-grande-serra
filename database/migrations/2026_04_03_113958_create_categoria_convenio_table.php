<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Cria a tabela de categorias de convênio.
 *
 * Padroniza os tipos de convênio que podem financiar uma obra.
 * Exemplos: "Pavimentação", "Saneamento Básico", "Saúde".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categoria_convenios', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100)->unique();   // era nome_categoria
            $table->text('descricao')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categoria_convenios');
    }
};
