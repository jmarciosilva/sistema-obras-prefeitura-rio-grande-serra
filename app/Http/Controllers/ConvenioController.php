<?php

namespace App\Http\Controllers;

use App\Models\Convenio;
use App\Models\Obra;
use App\Models\CategoriaConvenio;
use App\Models\OrgaoFinanciador;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConvenioController extends Controller
{
    public function index(Request $request): View
    {
        $convenios = Convenio::with(['categoria', 'orgaoFinanciador'])
            ->withCount('obras')
            ->when(
                $request->busca,
                fn ($q) => $q->where('descricao', 'like', "%{$request->busca}%")
                             ->orWhere('numero_convenio_ano', 'like', "%{$request->busca}%")
            )
            ->when(
                $request->orgao_id,
                fn ($q) => $q->where('orgao_financiador_id', $request->orgao_id)
            )
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $orgaos = OrgaoFinanciador::ordenados()->get();

        return view('convenios.index', compact('convenios', 'orgaos'));
    }

    public function show(Convenio $convenio): View
    {
        $convenio->load(['categoria', 'orgaoFinanciador', 'obras.status']);

        return view('convenios.show', compact('convenio'));
    }

    public function create(): View
    {
        $categorias = CategoriaConvenio::ordenadas()->get();
        $orgaos     = OrgaoFinanciador::ordenados()->get();

        return view('convenios.create', compact('categorias', 'orgaos'));
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'descricao'                   => 'required|string|max:500',
            'categoria_convenio_id'       => 'required|exists:categoria_convenios,id',
            'orgao_financiador_id'        => 'required|exists:orgaos_financiadores,id',
            'numero_convenio_ano'         => 'nullable|string|max:50',
            'processo_pref_concedente'    => 'nullable|string|max:100',
            'valor_repasse_contrapartida' => 'nullable|numeric|min:0',
            'assinatura'                  => 'nullable|date',
            'vigencia'                    => 'nullable|date',
            'cadastro_prescon_num'        => 'nullable|string|max:100',
        ]);

        try {
            DB::beginTransaction();

            $convenio = Convenio::create($dados);

            DB::commit();

            return redirect()
                ->route('convenios.show', $convenio)
                ->with('sucesso', 'Convênio cadastrado com sucesso!');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao criar convênio', ['erro' => $e->getMessage(), 'dados' => $dados]);

            return back()
                ->withInput()
                ->withErrors(['geral' => 'Ocorreu um erro ao salvar o convênio. Tente novamente.']);
        }
    }

    public function edit(Convenio $convenio): View
    {
        $categorias = CategoriaConvenio::ordenadas()->get();
        $orgaos     = OrgaoFinanciador::ordenados()->get();

        return view('convenios.edit', compact('convenio', 'categorias', 'orgaos'));
    }

    public function update(Request $request, Convenio $convenio): RedirectResponse
    {
        $dados = $request->validate([
            'descricao'                   => 'required|string|max:500',
            'categoria_convenio_id'       => 'required|exists:categoria_convenios,id',
            'orgao_financiador_id'        => 'required|exists:orgaos_financiadores,id',
            'numero_convenio_ano'         => 'nullable|string|max:50',
            'processo_pref_concedente'    => 'nullable|string|max:100',
            'valor_repasse_contrapartida' => 'nullable|numeric|min:0',
            'assinatura'                  => 'nullable|date',
            'vigencia'                    => 'nullable|date',
            'cadastro_prescon_num'        => 'nullable|string|max:100',
        ]);

        try {
            DB::beginTransaction();

            $convenio->update($dados);

            DB::commit();

            return redirect()
                ->route('convenios.show', $convenio)
                ->with('sucesso', 'Convênio atualizado com sucesso!');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar convênio', ['id' => $convenio->id, 'erro' => $e->getMessage()]);

            return back()
                ->withInput()
                ->withErrors(['geral' => 'Ocorreu um erro ao atualizar o convênio. Tente novamente.']);
        }
    }

    public function destroy(Convenio $convenio): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $convenio->obras()->detach();
            $convenio->delete();

            DB::commit();

            return redirect()
                ->route('convenios.index')
                ->with('sucesso', 'Convênio excluído com sucesso.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao excluir convênio', ['id' => $convenio->id, 'erro' => $e->getMessage()]);

            return back()->withErrors(['geral' => 'Ocorreu um erro ao excluir o convênio. Tente novamente.']);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // GESTÃO DE OBRAS VINCULADAS
    // ─────────────────────────────────────────────────────────────

    /**
     * Tela de gerenciamento: obras já vinculadas + disponíveis para vincular.
     * GET /convenios/{convenio}/obras
     */
    public function obras(Convenio $convenio): View
    {
        $convenio->load(['categoria', 'orgaoFinanciador', 'obras.status']);

        $vinculadas    = $convenio->obras;
        $vinculadasIds = $vinculadas->pluck('id');

        $disponiveis = Obra::with('status')
            ->whereNotIn('id', $vinculadasIds)
            ->orderBy('descricao')
            ->get();

        return view('convenios.vincular-obras', compact('convenio', 'vinculadas', 'disponiveis'));
    }

    /**
     * Vincula obras selecionadas ao convênio (additive — não remove as já existentes).
     * POST /convenios/{convenio}/obras/vincular
     */
    public function vincularObras(Request $request, Convenio $convenio): RedirectResponse
    {
        $request->validate([
            'obras'   => 'required|array|min:1',
            'obras.*' => 'exists:obras,id',
        ]);

        try {
            DB::beginTransaction();

            $convenio->obras()->syncWithoutDetaching($request->obras);

            DB::commit();

            $qtd = count($request->obras);

            return redirect()
                ->route('convenios.obras', $convenio)
                ->with('sucesso', "{$qtd} obra(s) vinculada(s) com sucesso!");

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao vincular obras ao convênio', [
                'convenio_id' => $convenio->id,
                'erro'        => $e->getMessage(),
            ]);

            return back()->withErrors(['geral' => 'Ocorreu um erro ao vincular as obras. Tente novamente.']);
        }
    }

    /**
     * Desvincula uma obra específica do convênio.
     * DELETE /convenios/{convenio}/obras/{obra}
     */
    public function desvincularObra(Convenio $convenio, Obra $obra): RedirectResponse
    {
        try {
            $convenio->obras()->detach($obra->id);

            return redirect()
                ->route('convenios.obras', $convenio)
                ->with('sucesso', "Obra \"{$obra->descricao}\" desvinculada com sucesso.");

        } catch (\Throwable $e) {
            Log::error('Erro ao desvincular obra do convênio', [
                'convenio_id' => $convenio->id,
                'obra_id'     => $obra->id,
                'erro'        => $e->getMessage(),
            ]);

            return back()->withErrors(['geral' => 'Não foi possível desvincular a obra. Tente novamente.']);
        }
    }
}