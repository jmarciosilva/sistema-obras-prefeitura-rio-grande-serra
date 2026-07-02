<?php

namespace App\Http\Controllers;

use App\Models\FaseProcesso;
use App\Models\Processo;
use App\Models\ResponsavelTecnico;
use App\Models\TipoProcesso;
use App\Services\Importacao\NumeroProcessoNormalizer;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessoController extends Controller
{
    // ── Index ──────────────────────────────────────────────────────
    public function index(Request $request): View
    {
        $processos = Processo::with(['tipoProcesso', 'faseAtual', 'responsavelTecnico'])
            ->when($request->tipo_processo_id, fn($q) => $q->where('tipo_processo_id', $request->tipo_processo_id))
            ->when($request->responsavel_tecnico_id, fn($q) => $q->where('responsavel_tecnico_id', $request->responsavel_tecnico_id))
            ->when($request->fase_atual_id, fn($q) => $q->where('fase_atual_id', $request->fase_atual_id))
            ->when($request->situacao, fn($q) => $q->where('situacao', $request->situacao))
            ->when($request->endereco, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('endereco', 'like', "%{$request->endereco}%")
                    ->orWhere('bairro', 'like', "%{$request->endereco}%")
                    ->orWhere('cidade', 'like', "%{$request->endereco}%");
            }))
            ->when($request->busca, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('processo_numero', 'like', "%{$request->busca}%")
                    ->orWhere('requerente', 'like', "%{$request->busca}%");
            }))
            ->latest('data_entrada')
            ->paginate(15)
            ->withQueryString();

        $tiposProcesso = TipoProcesso::ordenados()->get();
        $fasesProcesso = FaseProcesso::ordenados()->get();
        $responsaveis  = ResponsavelTecnico::ordenados()->get();

        return view('processos.index', compact('processos', 'tiposProcesso', 'fasesProcesso', 'responsaveis'));
    }

    // ── Show ───────────────────────────────────────────────────────
    public function show(Processo $processo): View
    {
        $processo->load([
            'tipoProcesso',
            'faseAtual',
            'responsavelTecnico',
            'tramites.faseProcesso',
        ]);

        $fasesProcesso = FaseProcesso::ordenados()->get();

        return view('processos.show', compact('processo', 'fasesProcesso'));
    }

    // ── Create ─────────────────────────────────────────────────────
    public function create(): View
    {
        $tiposProcesso = TipoProcesso::ordenados()->get();
        $fasesProcesso = FaseProcesso::ordenados()->get();
        $responsaveis  = ResponsavelTecnico::ordenados()->get();

        return view('processos.create', compact('tiposProcesso', 'fasesProcesso', 'responsaveis'));
    }

    // ── Store ──────────────────────────────────────────────────────
    public function store(Request $request): RedirectResponse
    {
        $dados = $this->validarDados($request);

        try {
            DB::beginTransaction();

            $dados['processo_numero_normalizado'] = $this->normalizarNumero($dados['processo_numero']);

            $processo = Processo::create($dados);

            DB::commit();

            return redirect()
                ->route('processos.show', $processo)
                ->with('sucesso', 'Processo cadastrado com sucesso!');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao criar processo', ['erro' => $e->getMessage(), 'dados' => $dados]);

            return back()
                ->withInput()
                ->withErrors(['geral' => 'Ocorreu um erro ao salvar o processo. Tente novamente.']);
        }
    }

    // ── Edit ───────────────────────────────────────────────────────
    public function edit(Processo $processo): View
    {
        $tiposProcesso = TipoProcesso::ordenados()->get();
        $fasesProcesso = FaseProcesso::ordenados()->get();
        $responsaveis  = ResponsavelTecnico::ordenados()->get();

        return view('processos.edit', compact('processo', 'tiposProcesso', 'fasesProcesso', 'responsaveis'));
    }

    // ── Update ─────────────────────────────────────────────────────
    public function update(Request $request, Processo $processo): RedirectResponse
    {
        $dados = $this->validarDados($request);

        try {
            DB::beginTransaction();

            $dados['processo_numero_normalizado'] = $this->normalizarNumero($dados['processo_numero']);

            $processo->update($dados);

            DB::commit();

            return redirect()
                ->route('processos.show', $processo)
                ->with('sucesso', 'Processo atualizado com sucesso!');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar processo', ['id' => $processo->id, 'erro' => $e->getMessage()]);

            return back()
                ->withInput()
                ->withErrors(['geral' => 'Ocorreu um erro ao atualizar o processo. Tente novamente.']);
        }
    }

    // ── Destroy ────────────────────────────────────────────────────
    public function destroy(Processo $processo): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $processo->delete();

            DB::commit();

            return redirect()
                ->route('processos.index')
                ->with('sucesso', 'Processo excluído com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao excluir processo', ['id' => $processo->id, 'erro' => $e->getMessage()]);

            return back()->withErrors(['geral' => 'Não foi possível excluir o processo.']);
        }
    }

    // ─────────────────────────────────────────────────
    // HELPERS PRIVADOS
    // ─────────────────────────────────────────────────

    private function validarDados(Request $request): array
    {
        return $request->validate([
            'processo_numero'        => 'required|string|max:100',
            'requerente'              => 'required|string|max:300',
            'cep'                     => 'nullable|string|regex:/^\d{5}-?\d{3}$/',
            'endereco'                => 'nullable|string|max:300',
            'numero'                  => 'nullable|string|max:20',
            'complemento'             => 'nullable|string|max:150',
            'bairro'                  => 'nullable|string|max:150',
            'cidade'                  => 'nullable|string|max:150',
            'uf'                      => 'nullable|string|size:2',
            'tipo_processo_id'        => 'required|exists:tipos_processo,id',
            'responsavel_tecnico_id'  => 'nullable|exists:responsaveis_tecnicos,id',
            'data_entrada'            => 'required|date',
            'fase_atual_id'           => 'required|exists:fases_processo,id',
            'setor_atual'             => 'nullable|string|max:100',
            'caixa_atual'             => 'nullable|string|max:100',
            'motivo_pendencia'        => 'nullable|string|max:2000',
            'situacao'                => 'required|in:aberto,arquivado',
        ]);
    }

    private function normalizarNumero(string $numero): string
    {
        return NumeroProcessoNormalizer::normalizar($numero);
    }
}
