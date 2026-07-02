<?php

namespace App\Http\Controllers;

use App\Models\Processo;
use App\Models\Tramite;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Trâmites são a linha do tempo de movimentação de um processo administrativo.
 *
 * A cada novo trâmite, a fase escolhida no formulário passa a ser a fase_atual do
 * processo — é essa fase estruturada que responde à pergunta do Secretário
 * ("em qual fase está o processo, e por quê"), conforme roadmap, seção 3.1
 * ("Mudança de prioridade após o áudio").
 */
class TramiteController extends Controller
{
    // ── Store ──────────────────────────────────────────────────────
    public function store(Request $request, Processo $processo): RedirectResponse
    {
        $dados = $request->validate([
            'fase_id'          => 'required|exists:fases_processo,id',
            'descricao'        => 'required|string',
            'data'             => 'nullable|date',
            'setor_destino'    => 'nullable|string|max:100',
            'tipo_evento'      => 'nullable|string|max:100',
            'motivo_pendencia' => 'nullable|string|max:2000',
        ]);

        try {
            DB::beginTransaction();

            $processo->tramites()->create([
                'data'          => $dados['data'] ?? now(),
                'descricao'     => $dados['descricao'],
                'fase_id'       => $dados['fase_id'],
                'setor_destino' => $dados['setor_destino'] ?? null,
                'tipo_evento'   => $dados['tipo_evento'] ?? null,
            ]);

            // A fase escolhida no trâmite passa a ser a fase atual do processo.
            $processo->fase_atual_id = $dados['fase_id'];

            if (! empty($dados['setor_destino'])) {
                $processo->setor_atual = $dados['setor_destino'];
            }

            if (! empty($dados['motivo_pendencia'])) {
                $processo->motivo_pendencia = $dados['motivo_pendencia'];
            }

            $processo->save();

            DB::commit();

            return redirect()
                ->route('processos.show', $processo)
                ->with('sucesso', 'Trâmite registrado com sucesso!');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao registrar trâmite', ['processo_id' => $processo->id, 'erro' => $e->getMessage()]);

            return back()
                ->withInput()
                ->withErrors(['geral' => 'Ocorreu um erro ao registrar o trâmite. Tente novamente.']);
        }
    }

    // ── Destroy ────────────────────────────────────────────────────
    public function destroy(Processo $processo, Tramite $tramite): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $tramite->delete();

            // Recalcula a fase atual a partir do trâmite mais recente restante.
            $ultimo = $processo->tramites()->first();

            if ($ultimo && $ultimo->fase_id) {
                $processo->fase_atual_id = $ultimo->fase_id;
                $processo->save();
            }

            DB::commit();

            return redirect()
                ->route('processos.show', $processo)
                ->with('sucesso', 'Trâmite excluído com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao excluir trâmite', ['id' => $tramite->id, 'erro' => $e->getMessage()]);

            return back()->withErrors(['geral' => 'Não foi possível excluir o trâmite.']);
        }
    }
}
