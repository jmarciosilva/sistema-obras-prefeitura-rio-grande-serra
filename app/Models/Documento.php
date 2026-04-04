<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

/**
 * Model: Documento anexado a Obras, Convênios ou Contratos.
 *
 * Utiliza polimorfismo (documentável) para que o mesmo model
 * sirva a múltiplas entidades sem duplicar tabelas.
 *
 * @property int    $id
 * @property string $documentavel_type   Classe do modelo pai
 * @property int    $documentavel_id     ID do modelo pai
 * @property int    $user_id             Quem fez o upload
 * @property string $tipo                (contrato|medicao|foto|ata|outros)
 * @property string $nome_original       Nome original do arquivo
 * @property string $caminho             Caminho no storage
 * @property string $mime_type
 * @property int    $tamanho_bytes
 * @property string|null $descricao
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Documento extends Model
{
    protected $table = 'documentos';

    protected $fillable = [
        'documentavel_type',
        'documentavel_id',
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

    const TIPO_CONTRATO  = 'contrato';
    const TIPO_MEDICAO   = 'medicao';
    const TIPO_FOTO      = 'foto';
    const TIPO_ATA       = 'ata';
    const TIPO_OUTROS    = 'outros';

    public static array $tipos = [
        self::TIPO_CONTRATO => 'Contrato',
        self::TIPO_MEDICAO  => 'Medição',
        self::TIPO_FOTO     => 'Foto / Registro',
        self::TIPO_ATA      => 'Ata / Ofício',
        self::TIPO_OUTROS   => 'Outros',
    ];

    // ── Relacionamentos ───────────────────────────────────────────

    /** Relação polimórfica — retorna a entidade pai (Obra, Convenio, Contrato...). */
    public function documentavel(): MorphTo
    {
        return $this->morphTo();
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // ── Accessors ────────────────────────────────────────────────

    /** URL pública para download. */
    public function getUrlAttribute(): string
    {
        return Storage::url($this->caminho);
    }

    /** Tamanho legível: "2,5 MB". */
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

    /** Ícone FontAwesome de acordo com MIME ou tipo. */
    public function getIconeAttribute(): string
    {
        return match (true) {
            str_contains($this->mime_type, 'pdf')   => 'fa-file-pdf text-danger',
            str_contains($this->mime_type, 'image') => 'fa-file-image text-info',
            str_contains($this->mime_type, 'word')  => 'fa-file-word text-primary',
            str_contains($this->mime_type, 'sheet'),
            str_contains($this->mime_type, 'excel') => 'fa-file-excel text-success',
            default                                  => 'fa-file text-secondary',
        };
    }

    // ── Scopes ───────────────────────────────────────────────────

    public function scopeTipo($query, string $tipo)
    {
        return $query->where('tipo', $tipo);
    }

    // ── Métodos de negócio ────────────────────────────────────────

    /** Remove o arquivo físico do storage ao deletar o registro. */
    protected static function booted(): void
    {
        static::deleting(function (Documento $doc) {
            Storage::delete($doc->caminho);
        });
    }
}
