<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Model: Empresa contratada. */
class Empresa extends Model
{
    protected $fillable = [
        'razao_social',
        'nome_fantasia',
        'cnpj',
        'telefone',
        'email',
        'responsavel',
        'endereco_completo',
    ];

    public function contratos(): HasMany
    {
        return $this->hasMany(Contrato::class);
    }

    /** Retorna nome fantasia se disponível, caso contrário a razão social. */
    public function nomeExibicao(): string
    {
        return $this->nome_fantasia ?: $this->razao_social;
    }
}
