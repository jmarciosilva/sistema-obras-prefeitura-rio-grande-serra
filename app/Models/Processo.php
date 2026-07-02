<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: Processo administrativo (licenciamento urbano — alvarás, certidões, ligações etc.).
 *
 * Núcleo do MVP (Fase 7.1): a `fase_atual_id` e o `motivo_pendencia` respondem à
 * pergunta central do Secretário — "em qual fase está o processo, e por quê".
 * Ambos são atualizados a cada novo trâmite lançado (ver TramiteController).
 */
class Processo extends Model
{
    protected $fillable = [
        'processo_numero',
        'processo_numero_normalizado',
        'requerente',
        'endereco',
        'cep',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'uf',
        'tipo_processo_id',
        'responsavel_tecnico_id',
        'data_entrada',
        'fase_atual_id',
        'setor_atual',
        'caixa_atual',
        'motivo_pendencia',
        'situacao',
    ];

    protected $casts = [
        'data_entrada' => 'date',
    ];

    const SITUACAO_ABERTO    = 'aberto';
    const SITUACAO_ARQUIVADO = 'arquivado';

    public static array $situacoes = [
        self::SITUACAO_ABERTO    => 'Aberto',
        self::SITUACAO_ARQUIVADO => 'Arquivado',
    ];

    // ── Relacionamentos ───────────────────────────────────────────

    public function tipoProcesso(): BelongsTo
    {
        return $this->belongsTo(TipoProcesso::class);
    }

    public function faseAtual(): BelongsTo
    {
        return $this->belongsTo(FaseProcesso::class, 'fase_atual_id');
    }

    public function responsavelTecnico(): BelongsTo
    {
        return $this->belongsTo(ResponsavelTecnico::class);
    }

    public function tramites(): HasMany
    {
        return $this->hasMany(Tramite::class)->orderByDesc('data')->orderByDesc('id');
    }

    // ── Accessors ────────────────────────────────────────────────

    public function getSituacaoLabelAttribute(): string
    {
        return self::$situacoes[$this->situacao] ?? ucfirst($this->situacao);
    }

    /** Endereço completo em uma única linha, para listagens e exportações. */
    public function getEnderecoCompletoAttribute(): ?string
    {
        $logradouro = trim($this->endereco . ($this->numero ? ", {$this->numero}" : ''));

        if ($this->complemento) {
            $logradouro .= " - {$this->complemento}";
        }

        $cidadeUf = trim(implode('/', array_filter([$this->cidade, $this->uf])));

        $partes = array_filter([$logradouro ?: null, $this->bairro, $cidadeUf ?: null]);

        return $partes ? implode(' - ', $partes) : null;
    }
}
