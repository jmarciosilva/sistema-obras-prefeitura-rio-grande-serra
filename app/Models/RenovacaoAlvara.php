<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model: Renovação anual de alvará (aba RENOVAÇÃO_DE_ALVARÁ da planilha legada,
 * agrupada por mês do ano).
 *
 * `processo_id` é nullable pelo mesmo motivo de Desarquivamento — ver comentário lá.
 */
class RenovacaoAlvara extends Model
{
    protected $table = 'renovacoes_alvara';

    protected $fillable = [
        'processo_id',
        'processo_numero_bruto',
        'data_renovacao',
        'mes_referencia',
    ];

    protected $casts = [
        'data_renovacao' => 'date',
    ];

    public function processo(): BelongsTo
    {
        return $this->belongsTo(Processo::class);
    }
}
