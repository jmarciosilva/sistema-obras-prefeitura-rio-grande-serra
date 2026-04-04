<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Problemas corrigidos vs migration original:
 *  - "nome_original"   → OK (mantido)
 *  - "caminho_arquivo" → "caminho"      (Model usa $this->caminho / Storage::delete($doc->caminho))
 *  - "tipo_mime"       → "mime_type"    (Model usa $this->mime_type)
 *  - "uploaded_by"     → "user_id"      (Model usa belongsTo(User, 'user_id'))
 *  - adicionado "tipo" (contrato|medicao|foto|ata|outros) (DocumentoController valida 'tipo')
 *  - tamanho_bytes mantido
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();

            // Relação polimórfica
            $table->morphs('documentable');

            $table->foreignId('user_id')                         // era uploaded_by
                ->constrained('users')
                ->restrictOnDelete();

            $table->string('tipo', 30)->default('outros');       // novo — contrato|medicao|foto|ata|outros
            $table->string('nome_original');
            $table->string('caminho', 500);                      // era caminho_arquivo
            $table->string('mime_type', 100)->nullable();        // era tipo_mime
            $table->unsignedBigInteger('tamanho_bytes')->nullable();
            $table->string('descricao', 300)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
