<?php

namespace App\Http\Controllers;

use App\Models\ExecucaoObra;
use App\Models\Obra;
use App\Models\Contrato;
use App\Models\Documento;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ExecucaoObraController extends Controller
{
    /**
     * Listagem de todas as medições de uma obra.
     */
    public function index(Obra $obra): View
    {
        $obra->load(['contratos.empresa']);

        $execucoes = ExecucaoObra::with(['contrato.empresa', 'responsaveis', 'documentos'])
            ->whereHas('contrato', fn($q) => $q->where('obra_id', $obra->id))
            ->orderByDesc('data_medicao')
            ->orderByDesc('id')
            ->paginate(20);

        $totalMedido     = ExecucaoObra::whereHas('contrato', fn($q) => $q->where('obra_id', $obra->id))->sum('valor_medido');
        $valorContrato   = $obra->contratos->sum('valor_contrato');
        $percentualGeral = $valorContrato > 0 ? min(($totalMedido / $valorContrato) * 100, 100) : 0;
        $saldoGeral      = max($valorContrato - $totalMedido, 0);

        return view('execucoes.index', compact(
            'obra',
            'execucoes',
            'totalMedido',
            'valorContrato',
            'percentualGeral',
            'saldoGeral'
        ));
    }

    /**
     * Formulário de nova medição.
     */
    public function create(Obra $obra): View
    {
        $contratos = Contrato::with('empresa')
            ->where('obra_id', $obra->id)
            ->orderBy('numero_contrato_ano')
            ->get();

        $saldosPorContrato = $contratos->mapWithKeys(fn($c) => [
            $c->id => [
                'valor_contrato' => (float) ($c->valor_contrato ?? 0),
                'total_medido'   => (float) $c->execucoes()->sum('valor_medido'),
                'saldo'          => max((float) ($c->valor_contrato ?? 0) - (float) $c->execucoes()->sum('valor_medido'), 0),
            ],
        ]);

        // Usuários disponíveis para ser responsáveis (admin e técnico)
        $usuarios = User::whereIn('perfil', ['admin', 'tecnico', 'secretario'])
            ->where('ativo', true)
            ->orderBy('name')
            ->get();

        $tiposDocumento = Documento::$tipos;

        return view('execucoes.create', compact('obra', 'contratos', 'saldosPorContrato', 'usuarios', 'tiposDocumento'));
    }

    /**
     * Persiste nova medição com cálculo automático + responsáveis + documentos.
     */
    public function store(Request $request, Obra $obra): RedirectResponse
    {
        $dados = $request->validate([
            'contrato_id'    => 'required|exists:contratos,id',
            'data_medicao'   => 'required|date',
            'valor_medido'   => 'required|numeric|min:0.01',
            'observacao'     => 'nullable|string|max:1000',
            // Responsáveis
            'responsaveis'          => 'nullable|array',
            'responsaveis.*.user_id' => 'required|exists:users,id',
            'responsaveis.*.papel'   => 'required|in:engenheiro,fiscal,supervisor',
            // Documentos
            'arquivos'              => 'nullable|array|max:10',
            'arquivos.*'            => 'file|max:20480|mimes:pdf,jpg,jpeg,png,gif,webp,xlsx,xls,csv,docx,doc',
            'arquivos_tipo'         => 'nullable|array',
            'arquivos_tipo.*'       => 'in:contrato,medicao,foto,ata,outros',
            'arquivos_descricao'    => 'nullable|array',
            'arquivos_descricao.*'  => 'nullable|string|max:300',
        ]);

        // Garante que o contrato pertence à obra
        $contrato = Contrato::where('id', $dados['contrato_id'])
            ->where('obra_id', $obra->id)
            ->firstOrFail();

        try {
            DB::beginTransaction();

            // Cálculo automático
            $totalAnterior = (float) $contrato->execucoes()->sum('valor_medido');
            $valorMedido   = $this->normalizarMoeda($dados['valor_medido']);
            $totalComNova  = $totalAnterior + $valorMedido;

            $execucao = ExecucaoObra::create([
                'contrato_id'          => $dados['contrato_id'],
                'data_medicao'         => $dados['data_medicao'],
                'valor_medido'         => $valorMedido,
                'observacao'           => $dados['observacao'] ?? null,
                'saldo_contratual'     => max(($contrato->valor_contrato ?? 0) - $totalComNova, 0),
                'percentual_executado' => $contrato->valor_contrato > 0
                    ? min(($totalComNova / $contrato->valor_contrato) * 100, 100)
                    : null,
            ]);

            // Responsáveis
            if (!empty($dados['responsaveis'])) {
                $syncData = [];
                foreach ($dados['responsaveis'] as $resp) {
                    $syncData[$resp['user_id']] = ['papel' => $resp['papel']];
                }
                $execucao->responsaveis()->sync($syncData);
            }

            // Documentos
            if ($request->hasFile('arquivos')) {
                foreach ($request->file('arquivos') as $i => $arquivo) {
                    $caminho = $arquivo->store("documentos/medicoes/{$execucao->id}", 'public');

                    $execucao->documentos()->create([
                        'user_id'       => auth()->id(),
                        'tipo'          => $dados['arquivos_tipo'][$i] ?? 'medicao',
                        'nome_original' => $arquivo->getClientOriginalName(),
                        'caminho'       => $caminho,
                        'mime_type'     => $arquivo->getMimeType(),
                        'tamanho_bytes' => $arquivo->getSize(),
                        'descricao'     => $dados['arquivos_descricao'][$i] ?? null,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('obras.show', $obra)
                ->with('sucesso', 'Medição registrada com sucesso!')
                ->with('aba', 'execucoes');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao registrar medição', [
                'obra_id'     => $obra->id,
                'contrato_id' => $dados['contrato_id'],
                'erro'        => $e->getMessage(),
            ]);

            return back()->withInput()
                ->withErrors(['geral' => 'Ocorreu um erro ao salvar a medição. Tente novamente.']);
        }
    }

    /**
     * Formulário de edição.
     */
    public function edit(Obra $obra, ExecucaoObra $execucao): View
    {
        $execucao->load(['responsaveis', 'documentos']);

        $contratos = Contrato::with('empresa')
            ->where('obra_id', $obra->id)
            ->orderBy('numero_contrato_ano')
            ->get();

        $saldosPorContrato = $contratos->mapWithKeys(function ($c) use ($execucao) {
            $totalSemEsta = (float) $c->execucoes()->where('id', '!=', $execucao->id)->sum('valor_medido');
            $saldo        = max((float) ($c->valor_contrato ?? 0) - $totalSemEsta, 0);

            return [$c->id => [
                'valor_contrato' => (float) ($c->valor_contrato ?? 0),
                'total_medido'   => $totalSemEsta,
                'saldo'          => $saldo,
            ]];
        });

        $usuarios = User::whereIn('perfil', ['admin', 'tecnico', 'secretario'])
            ->where('ativo', true)
            ->orderBy('name')
            ->get();

        // Responsáveis já vinculados: [ user_id => papel ]
        $responsaveisAtuais = $execucao->responsaveis
            ->mapWithKeys(fn($u) => [$u->id => $u->pivot->papel]);

        $tiposDocumento = Documento::$tipos;

        return view('execucoes.edit', compact(
            'obra',
            'execucao',
            'contratos',
            'saldosPorContrato',
            'usuarios',
            'responsaveisAtuais',
            'tiposDocumento'
        ));
    }

    /**
     * Atualiza medição com recálculo de saldo/percentual + responsáveis + documentos.
     */
    public function update(Request $request, Obra $obra, ExecucaoObra $execucao): RedirectResponse
    {
        $dados = $request->validate([
            'contrato_id'           => 'required|exists:contratos,id',
            'data_medicao'          => 'required|date',
            'valor_medido'          => 'required|numeric|min:0.01',
            'observacao'            => 'nullable|string|max:1000',
            'responsaveis'          => 'nullable|array',
            'responsaveis.*.user_id' => 'required|exists:users,id',
            'responsaveis.*.papel'   => 'required|in:engenheiro,fiscal,supervisor',
            'arquivos'              => 'nullable|array|max:10',
            'arquivos.*'            => 'file|max:20480|mimes:pdf,jpg,jpeg,png,gif,webp,xlsx,xls,csv,docx,doc',
            'arquivos_tipo'         => 'nullable|array',
            'arquivos_tipo.*'       => 'in:contrato,medicao,foto,ata,outros',
            'arquivos_descricao'    => 'nullable|array',
            'arquivos_descricao.*'  => 'nullable|string|max:300',
        ]);

        $contrato = Contrato::where('id', $dados['contrato_id'])
            ->where('obra_id', $obra->id)
            ->firstOrFail();

        try {
            DB::beginTransaction();

            $valorMedido  = $this->normalizarMoeda($dados['valor_medido']);
            $totalSemEsta = (float) $contrato->execucoes()->where('id', '!=', $execucao->id)->sum('valor_medido');
            $totalComNova = $totalSemEsta + $valorMedido;

            $execucao->update([
                'contrato_id'          => $dados['contrato_id'],
                'data_medicao'         => $dados['data_medicao'],
                'valor_medido'         => $valorMedido,
                'observacao'           => $dados['observacao'] ?? null,
                'saldo_contratual'     => max(($contrato->valor_contrato ?? 0) - $totalComNova, 0),
                'percentual_executado' => $contrato->valor_contrato > 0
                    ? min(($totalComNova / $contrato->valor_contrato) * 100, 100)
                    : null,
            ]);

            // Responsáveis — substitui todos
            $syncData = [];
            foreach ($dados['responsaveis'] ?? [] as $resp) {
                $syncData[$resp['user_id']] = ['papel' => $resp['papel']];
            }
            $execucao->responsaveis()->sync($syncData);

            // Novos documentos
            if ($request->hasFile('arquivos')) {
                foreach ($request->file('arquivos') as $i => $arquivo) {
                    $caminho = $arquivo->store("documentos/medicoes/{$execucao->id}", 'public');

                    $execucao->documentos()->create([
                        'user_id'       => auth()->id(),
                        'tipo'          => $dados['arquivos_tipo'][$i] ?? 'medicao',
                        'nome_original' => $arquivo->getClientOriginalName(),
                        'caminho'       => $caminho,
                        'mime_type'     => $arquivo->getMimeType(),
                        'tamanho_bytes' => $arquivo->getSize(),
                        'descricao'     => $dados['arquivos_descricao'][$i] ?? null,
                    ]);
                }
            }

            DB::commit();

            return redirect()
                ->route('obras.show', $obra)
                ->with('sucesso', 'Medição atualizada com sucesso!')
                ->with('aba', 'execucoes');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar medição', ['execucao_id' => $execucao->id, 'erro' => $e->getMessage()]);

            return back()->withInput()
                ->withErrors(['geral' => 'Ocorreu um erro ao atualizar a medição. Tente novamente.']);
        }
    }

    /**
     * Remove medição e seus documentos físicos.
     */
    public function destroy(Obra $obra, ExecucaoObra $execucao): RedirectResponse
    {
        try {
            DB::beginTransaction();

            // Documentos físicos são removidos pelo model event Documento::deleting
            $execucao->documentos->each->delete();
            $execucao->responsaveis()->detach();
            $execucao->delete();

            DB::commit();

            return back()->with('sucesso', 'Medição excluída com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao excluir medição', ['execucao_id' => $execucao->id, 'erro' => $e->getMessage()]);

            return back()->withErrors(['geral' => 'Não foi possível excluir a medição.']);
        }
    }

    /**
     * Remove um documento específico de uma medição.
     * DELETE /obras/{obra}/execucoes/{execucao}/documentos/{documento}
     */
    public function destroyDocumento(Obra $obra, ExecucaoObra $execucao, Documento $documento): RedirectResponse
    {
        abort_unless($documento->documentable_id === $execucao->id, 403);

        $documento->delete(); // model event remove o arquivo físico

        return back()->with('sucesso', 'Documento removido.');
    }

    /**
     * Download de documento de medição.
     * GET /obras/{obra}/execucoes/{execucao}/documentos/{documento}/download
     */
    public function downloadDocumento(Obra $obra, ExecucaoObra $execucao, Documento $documento)
    {
        abort_unless($documento->documentable_id === $execucao->id, 403);
        abort_unless(Storage::disk('public')->exists($documento->caminho), 404);

        return Storage::disk('public')->download($documento->caminho, $documento->nome_original);
    }

    // ─────────────────────────────────────────────────────────────
    private function normalizarMoeda(mixed $valor): float
    {
        if (is_string($valor) && str_contains($valor, ',')) {
            $valor = str_replace('.', '', $valor);
            $valor = str_replace(',', '.', $valor);
        }
        return (float) $valor;
    }
}
