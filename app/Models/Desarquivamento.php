<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model: Solicitação de desarquivamento de um processo (aba DESARQUIVAMENTO da planilha legada).
 *
 * `processo_id` é nullable — nem todo registro importado da planilha bate com um
 * processo já cadastrado; nesse caso o número original fica em `processo_numero_bruto`
 * para curadoria manual.
 */
class Desarquivamento extends Model
{
    protected $fillable = [
        'processo_id',
        'processo_numero_bruto',
        'data_solicitacao',
        'motivo',
    ];

    protected $casts = [
        'data_solicitacao' => 'date',
    ];

    public function processo(): BelongsTo
    {
        return $this->belongsTo(Processo::class);
    }
}
