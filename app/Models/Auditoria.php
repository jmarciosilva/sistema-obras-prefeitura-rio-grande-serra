<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Histórico de atividades (criação, alteração e exclusão) do sistema web.
 * Gravado por App\Services\AuditoriaService — nunca editado pela interface.
 */
class Auditoria extends Model
{
    protected $table = 'auditorias';

    public const CRIOU   = 'criou';
    public const ALTEROU = 'alterou';
    public const EXCLUIU = 'excluiu';

    public const ACOES = [
        self::CRIOU   => 'Criou',
        self::ALTEROU => 'Alterou',
        self::EXCLUIU => 'Excluiu',
    ];

    /** Chave usada na URL/filtro → classe auditada → rótulo. */
    public const MODULOS = [
        'obra'     => ['classe' => Obra::class,         'rotulo' => 'Obra'],
        'contrato' => ['classe' => Contrato::class,     'rotulo' => 'Contrato'],
        'medicao'  => ['classe' => ExecucaoObra::class, 'rotulo' => 'Medição'],
    ];

    protected $fillable = [
        'user_id',
        'acao',
        'auditable_type',
        'auditable_id',
        'descricao',
        'dados_antes',
        'dados_depois',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'dados_antes'  => 'array',
        'dados_depois' => 'array',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getAcaoLabelAttribute(): string
    {
        return self::ACOES[$this->acao] ?? ucfirst($this->acao);
    }

    public function getModuloLabelAttribute(): string
    {
        foreach (self::MODULOS as $modulo) {
            if ($modulo['classe'] === $this->auditable_type) {
                return $modulo['rotulo'];
            }
        }

        return class_basename($this->auditable_type);
    }

    /**
     * Filtros da tela "Histórico de Atividades".
     * Aceita: usuario, de, ate (Y-m-d), modulo (obra|contrato|medicao), acao.
     */
    public function scopeFiltrar(Builder $query, array $filtros): Builder
    {
        return $query
            ->when($filtros['usuario'] ?? null, fn($q, $id) => $q->where('user_id', $id))
            ->when($filtros['de'] ?? null, fn($q, $de) => $q->where('created_at', '>=', $de . ' 00:00:00'))
            ->when($filtros['ate'] ?? null, fn($q, $ate) => $q->where('created_at', '<=', $ate . ' 23:59:59'))
            ->when(
                self::MODULOS[$filtros['modulo'] ?? ''] ?? null,
                fn($q, $modulo) => $q->where('auditable_type', $modulo['classe'])
            )
            ->when(
                array_key_exists($filtros['acao'] ?? '', self::ACOES) ? $filtros['acao'] : null,
                fn($q, $acao) => $q->where('acao', $acao)
            );
    }
}
