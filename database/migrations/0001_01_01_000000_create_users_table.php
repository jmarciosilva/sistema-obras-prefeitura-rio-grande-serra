<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 🚀 Executa as migrations
     */
    public function up(): void
    {
        // ─────────────────────────────────────────────
        // 👤 TABELA DE USUÁRIOS
        // ─────────────────────────────────────────────
        Schema::create('users', function (Blueprint $table) {

            $table->id();

            // Dados básicos
            $table->string('name'); // Nome completo
            $table->string('email')->unique(); // Email (login)
            $table->timestamp('email_verified_at')->nullable();

            // Autenticação
            $table->string('password');
            $table->rememberToken();

            // 🔐 Controle de acesso
            $table->enum('perfil', [
                'admin',
                'secretario',
                'operador',
                'tecnico'
            ])->default('operador');

            $table->boolean('ativo')->default(true); // Controle de acesso

            // 📞 Contatos
            $table->string('telefone')->nullable()
                  ->comment('Telefone fixo da prefeitura (com DDD)');

            $table->string('ramal', 10)->nullable()
                  ->comment('Ramal interno da prefeitura');

            $table->string('whatsapp')->nullable()
                  ->comment('Telefone celular/WhatsApp para contato');

            // Datas padrão
            $table->timestamps();
        });

        // ─────────────────────────────────────────────
        // 🔑 RESET DE SENHA
        // ─────────────────────────────────────────────
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // ─────────────────────────────────────────────
        // 🧠 SESSÕES
        // ─────────────────────────────────────────────
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * 🔄 Reverte as migrations
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};