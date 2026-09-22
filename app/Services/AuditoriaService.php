<?php

namespace App\Services;

use App\Models\Auditoria;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Registra o histórico de atividades (tabela `auditorias`).
 *
 * - Os dados de antes/depois são capturados NA HORA do evento (depois dele o
 *   Eloquent sincroniza o "original" e o valor anterior se perde).
 * - A gravação só acontece depois do COMMIT da transação em andamento: uma
 *   operação desfeita (rollback) não gera histórico. Sem transação, grava na hora.
 * - Nunca interrompe a operação principal: falhas vão apenas para o log.
 */
class AuditoriaService
{
    /** Campos técnicos que não interessam ao histórico. */
    private const IGNORADOS = ['id', 'created_at', 'updated_at', 'deleted_at'];

    /** Nunca gravar credenciais, mesmo que o service seja reutilizado. */
    private const SENSIVEIS = [
        'password', 'senha', 'remember_token', 'token', 'api_token',
        'credenciais', 'two_factor_secret', 'two_factor_recovery_codes',
    ];

    public function registrar(
        string $acao,
        Model $model,
        ?array $antes = null,
        ?array $depois = null,
        ?string $descricao = null,
    ): void {
        try {
            $registro = [
                'user_id'        => auth()->id(),
                'acao'           => $acao,
                'auditable_type' => $model::class,
                'auditable_id'   => $model->getKey(),
                'descricao'      => $descricao ? Str::limit($descricao, 250) : null,
                'dados_antes'    => $antes === null ? null : self::limpar($antes),
                'dados_depois'   => $depois === null ? null : self::limpar($depois),
                'ip_address'     => request()?->ip(),
                'user_agent'     => request()?->userAgent(),
            ];

            DB::afterCommit(function () use ($registro) {
                try {
                    Auditoria::create($registro);
                } catch (\Throwable $e) {
                    self::falhou($e, $registro);
                }
            });
        } catch (\Throwable $e) {
            self::falhou($e, ['acao' => $acao, 'auditable_type' => $model::class, 'auditable_id' => $model->getKey()]);
        }
    }

    /** created: só "depois", com os dados relevantes do registro. */
    public function criado(Model $model, string $descricao): void
    {
        $this->registrar(Auditoria::CRIOU, $model, null, $model->getAttributes(), $descricao);
    }

    /** updated: somente os campos que realmente mudaram (antes → depois). */
    public function alterado(Model $model, string $descricao): void
    {
        $depois = self::limpar($model->getChanges());
        if ($depois === []) {
            return; // só timestamps mudaram
        }

        $antes = array_intersect_key($model->getRawOriginal(), $depois);

        $this->registrar(Auditoria::ALTEROU, $model, $antes, $depois, $descricao);
    }

    /** deleted: snapshot completo de antes da exclusão. */
    public function excluido(Model $model, string $descricao): void
    {
        $this->registrar(Auditoria::EXCLUIU, $model, $model->getRawOriginal(), null, $descricao);
    }

    /** Remove campos técnicos e sensíveis. */
    public static function limpar(array $dados): array
    {
        return array_filter(
            $dados,
            function ($campo) {
                $campo = strtolower((string) $campo);
                return ! in_array($campo, self::IGNORADOS, true)
                    && ! in_array($campo, self::SENSIVEIS, true)
                    && ! str_contains($campo, 'password')
                    && ! str_contains($campo, 'token');
            },
            ARRAY_FILTER_USE_KEY,
        );
    }

    private static function falhou(\Throwable $e, array $contexto): void
    {
        Log::error('Falha ao registrar auditoria', [
            'erro'     => $e->getMessage(),
            'acao'     => $contexto['acao'] ?? null,
            'registro' => ($contexto['auditable_type'] ?? '?') . '#' . ($contexto['auditable_id'] ?? '?'),
        ]);
    }
}
