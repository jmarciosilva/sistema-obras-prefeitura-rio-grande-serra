<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Model: Convênio.
 * Instrumento jurídico entre a prefeitura e o órgão financiador.
 */
class Convenio extends Model
{
    protected $fillable = [
        'categoria_convenio_id',
        'orgao_financiador_id',
        'demanda_proposta_id',
        'descricao',
        'processo_pref_concedente',
        'numero_convenio_ano',
        'valor_repasse_contrapartida',
        'assinatura',
        'vigencia',
        'cadastro_prescon_num',
    ];

    protected $casts = [
        'assinatura' => 'date',
        'vigencia'   => 'date',
        'valor_repasse_contrapartida' => 'decimal:2',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaConvenio::class, 'categoria_convenio_id');
    }

    public function orgaoFinanciador(): BelongsTo
    {
        return $this->belongsTo(OrgaoFinanciador::class, 'orgao_financiador_id');
    }

    public function demandaProposta(): BelongsTo
    {
        return $this->belongsTo(DemandaProposta::class, 'demanda_proposta_id');
    }

    /**
     * Obras financiadas por este convênio (N:M).
     */
    public function obras(): BelongsToMany
    {
        return $this->belongsToMany(Obra::class, 'obra_convenio');
    }
}
