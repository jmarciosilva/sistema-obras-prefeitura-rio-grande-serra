<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration: Cria a tabela de status das obras.
 *
 * Tabela auxiliar (lookup) que padroniza os possíveis estados de uma obra.
 * O campo `cor` é usado para colorir o badge visual no dashboard.
 * O campo `ordem` define a sequência de exibição nas listagens.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_obras', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 80)->unique();
            $table->string('cor', 20)->default('#6c757d');
            $table->unsignedTinyInteger('ordem')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_obras');
    }
};