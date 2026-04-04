<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Model: Usuário do sistema.
 *
 * Estende o model padrão do Laravel adicionando:
 *  - perfil: controla o que o usuário pode fazer (admin, secretario, operador, tecnico)
 *  - ativo:  permite desativar o acesso sem excluir o registro
 *
 * Hierarquia de perfis:
 *  admin      → acesso total, gerencia usuários e tabelas auxiliares
 *  secretario → visualiza dashboards completos e relatórios
 *  operador   → cadastra e edita obras, convênios, contratos e medições
 *  tecnico    → acesso de campo: registra medições e faz upload de fotos
 */
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * Campos que podem ser preenchidos em massa.
     * Nunca remova 'perfil' e 'ativo' daqui — são essenciais para controle de acesso.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'perfil',
        'ativo',
        'telefone',
        'ramal',
        'whatsapp',
    ];

    /**
     * Campos que nunca serão retornados em respostas JSON ou arrays.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Cast automático dos tipos de dados.
     * 'ativo' é cast para boolean para facilitar verificações como if ($user->ativo).
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'ativo'             => 'boolean',
        ];
    }

    // ─── Métodos auxiliares de verificação de perfil ──────────────────────────

    /**
     * Verifica se o usuário tem o perfil de administrador.
     */
    public function isAdmin(): bool
    {
        return $this->perfil === 'admin';
    }

    /**
     * Verifica se o usuário é secretário.
     */
    public function isSecretario(): bool
    {
        return $this->perfil === 'secretario';
    }

    /**
     * Verifica se o usuário é operador administrativo.
     */
    public function isOperador(): bool
    {
        return $this->perfil === 'operador';
    }

    /**
     * Verifica se o usuário é técnico de campo.
     */
    public function isTecnico(): bool
    {
        return $this->perfil === 'tecnico';
    }

    /**
     * Verifica se o usuário tem permissão de leitura administrativa.
     * Secretário e admin podem ver tudo mas nem sempre podem editar.
     */
    public function podeGerenciarCadastros(): bool
    {
        return in_array($this->perfil, ['admin', 'operador']);
    }

    /**
     * Verifica se o usuário pode acessar configurações do sistema.
     * Apenas o admin tem acesso às tabelas auxiliares e gerenciamento de usuários.
     */
    public function podeAcessarConfiguracoes(): bool
    {
        return $this->perfil === 'admin';
    }

    /**
     * Retorna o rótulo legível do perfil para exibição nas views.
     */
    public function labelPerfil(): string
    {
        return match ($this->perfil) {
            'admin'      => 'Administrador',
            'secretario' => 'Secretário de Obras',
            'operador'   => 'Operador Administrativo',
            'tecnico'    => 'Técnico de Campo',
            default      => 'Usuário',
        };
    }

    // ─── Relacionamentos ──────────────────────────────────────────────────────

    /**
     * Documentos enviados por este usuário.
     */
    public function documentos(): HasMany
    {
        return $this->hasMany(Documento::class, 'uploaded_by');
    }
}
