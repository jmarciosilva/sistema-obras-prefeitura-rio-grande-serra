<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

/**
 * Model: Documento anexado a Obras, Convênios, Contratos ou Medições.
 *
 * Utiliza polimorfismo (documentable) para que o mesmo model
 * sirva a múltiplas entidades sem duplicar tabelas.
 *
 * ⚠️  ATENÇÃO — grafia dos campos:
 * A tabela usa morphs('documentable') → colunas documentable_type / documentable_id
 * O $fillable DEVE usar a mesma grafia com B (não V).
 * Grafia errada bloqueia o mass-assignment e salva NULL nas colunas polimórficas.
 */
class Documento extends Model
{
    protected $table = 'documentos';

    protected $fillable = [
        'documentable_type',   // ← com B (correto — igual à coluna da tabela)
        'documentable_id',     // ← com B (correto — igual à coluna da tabela)
        'user_id',
        'tipo',
        'nome_original',
        'caminho',
        'mime_type',
        'tamanho_bytes',
        'descricao',
    ];

    protected $casts = [
        'tamanho_bytes' => 'integer',
    ];

    // ── Constantes ───────────────────────────────────────────────

    const TIPO_CONTRATO = 'contrato';
    const TIPO_MEDICAO  = 'medicao';
    const TIPO_FOTO     = 'foto';
    const TIPO_ATA      = 'ata';
    const TIPO_OUTROS   = 'outros';

    public static array $tipos = [
        self::TIPO_CONTRATO => 'Contrato',
        self::TIPO_MEDICAO  => 'Medição',
        self::TIPO_FOTO     => 'Foto / Registro',
        self::TIPO_ATA      => 'Ata / Ofício',
        self::TIPO_OUTROS   => 'Outros',
    ];

    // ── Relacionamentos ───────────────────────────────────────────

    /**
     * Relação polimórfica — retorna a entidade pai (Obra, Convenio, Contrato, ExecucaoObra...).
     * Usa 'documentable' com B, alinhado com morphs('documentable') na migration.
     */
    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ── Accessors ────────────────────────────────────────────────

    public function getUrlAttribute(): string
    {
        return Storage::url($this->caminho);
    }

    public function getTamanhoLegívelAttribute(): string
    {
        $bytes = $this->tamanho_bytes;

        if ($bytes >= 1_048_576) {
            return number_format($bytes / 1_048_576, 1, ',', '.') . ' MB';
        }

        if ($bytes >= 1_024) {
            return number_format($bytes / 1_024, 0, ',', '.') . ' KB';
        }

        return "{$bytes} bytes";
    }

    public function getTipoLabelAttribute(): string
    {
        return self::$tipos[$this->tipo] ?? ucfirst($this->tipo);
    }

    public function getIconeAttribute(): string
    {
        return match (true) {
            str_contains($this->mime_type ?? '', 'pdf')   => 'fa-file-pdf text-danger',
            str_contains($this->mime_type ?? '', 'image') => 'fa-file-image text-info',
            str_contains($this->mime_type ?? '', 'word')  => 'fa-file-word text-primary',
            str_contains($this->mime_type ?? '', 'sheet'),
            str_contains($this->mime_type ?? '', 'excel') => 'fa-file-excel text-success',
            default                                        => 'fa-file text-secondary',
        };
    }

    // ── Scopes ───────────────────────────────────────────────────

    public function scopeTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    // ── Eventos ──────────────────────────────────────────────────

    /** Remove o arquivo físico do storage ao deletar o registro. */
    protected static function booted(): void
    {
        static::deleting(function (Documento $doc) {
            if ($doc->caminho) {
                Storage::disk('public')->delete($doc->caminho);
            }
        });
    }
}
