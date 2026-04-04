<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model: Categoria do convênio (ex: Pavimentação, Saneamento, Saúde...).
 *
 * @property int    $id
 * @property string $nome
 * @property string|null $descricao
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class CategoriaConvenio extends Model
{
    protected $table = 'categoria_convenios';

    protected $fillable = [
        'nome',
        'descricao',
    ];

    // ── Relacionamentos ───────────────────────────────────────────

    public function convenios(): HasMany
    {
        return $this->hasMany(Convenio::class, 'categoria_convenio_id');
    }

    // ── Scopes ───────────────────────────────────────────────────

    /** Ordenadas alfabeticamente — útil para selects. */
    public function scopeOrdenadas($query)
    {
        return $query->orderBy('nome');
    }
}
