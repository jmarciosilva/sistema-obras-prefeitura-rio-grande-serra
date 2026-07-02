<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model: Trâmite — um registro de movimentação (texto livre) de um processo administrativo.
 *
 * Espelha o hábito real de trabalho (log cronológico em texto livre, hoje feito na
 * planilha CONTROLE_PROCESSOS.xlsx), mas exige a escolha de uma fase estruturada a
 * cada lançamento — é essa fase estruturada que alimenta o dashboard do Secretário.
 *
 * setor_destino e tipo_evento não são enum de banco (vocabulário ainda em aberto —
 * ver roadmap, seção 5). As listas abaixo são apenas sugestões de preenchimento.
 */
class Tramite extends Model
{
    protected $fillable = [
        'processo_id',
        'data',
        'descricao',
        'fase_id',
        'setor_destino',
        'tipo_evento',
        'importacao_ref',
    ];

    protected $casts = [
        'data' => 'date',
    ];

    // Sugestões de setor de destino (ver roadmap, seção 1.2) — lista não fechada.
    public static array $setoresSugeridos = [
        'Protocolo',
        'CTM',
        'SVMA/SECLIMA',
        'Fiscalização',
        'SAJ',
        'Secretário',
    ];

    // Sugestões de tipo de evento (ver roadmap, seção 1.2) — lista não fechada.
    public static array $tiposEventoSugeridos = [
        'Notificação (correio)',
        'Notificação (e-mail)',
        'Notificação (telefone)',
        'Vistoria',
        'Juntada de documento',
        'Emissão de boleto',
        'Arquivamento',
        'Desarquivamento',
    ];

    // ── Relacionamentos ───────────────────────────────────────────

    public function processo(): BelongsTo
    {
        return $this->belongsTo(Processo::class);
    }

    public function faseProcesso(): BelongsTo
    {
        return $this->belongsTo(FaseProcesso::class, 'fase_id');
    }
}
