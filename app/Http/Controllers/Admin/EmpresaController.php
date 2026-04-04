<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmpresaController extends Controller
{
    public function index(Request $request): View
    {
        $empresas = Empresa::when(
            $request->busca,
            fn($q) => $q->where('razao_social', 'like', "%{$request->busca}%")
                ->orWhere('nome_fantasia', 'like', "%{$request->busca}%")
                ->orWhere('cnpj', 'like', "%{$request->busca}%")
        )
            ->withCount('contratos')->orderBy('razao_social')
            ->paginate(15)
            ->withQueryString();

        return view('admin.empresas.index', compact('empresas'));
    }

    public function show(Empresa $empresa): View
    {
        $empresa->load('contratos.obra');

        return view('admin.empresas.show', compact('empresa'));
    }

    public function create(): View
    {
        return view('admin.empresas.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $dados = $request->validate([
            'razao_social'      => 'required|string|max:255',
            'nome_fantasia'     => 'nullable|string|max:255',
            'cnpj'              => 'required|string|size:18|unique:empresas,cnpj',
            'telefone'          => 'nullable|string|max:20',
            'email'             => 'nullable|email|max:255',
            'responsavel'       => 'nullable|string|max:150',
            'endereco_completo' => 'nullable|string|max:300',
        ]);

        try {
            DB::beginTransaction();

            Empresa::create($dados);

            DB::commit();

            // Suporte ao redirecionamento personalizado (ex: volta ao create de contrato)
            $redirectTo = $request->input('_redirect_back', route('admin.empresas.index'));

            return redirect($redirectTo)
                ->with('empresa_criada', "Empresa \"{$dados['razao_social']}\" cadastrada com sucesso!");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao criar empresa', ['erro' => $e->getMessage(), 'dados' => $dados]);

            return back()
                ->withInput()
                ->withErrors(['geral' => 'Ocorreu um erro ao salvar a empresa. Tente novamente.']);
        }
    }

    public function edit(Empresa $empresa): View
    {
        return view('admin.empresas.edit', compact('empresa'));
    }

    public function update(Request $request, Empresa $empresa): RedirectResponse
    {
        $dados = $request->validate([
            'razao_social'      => 'required|string|max:255',
            'nome_fantasia'     => 'nullable|string|max:255',
            'cnpj'              => "required|string|size:18|unique:empresas,cnpj,{$empresa->id}",
            'telefone'          => 'nullable|string|max:20',
            'email'             => 'nullable|email|max:255',
            'responsavel'       => 'nullable|string|max:150',
            'endereco_completo' => 'nullable|string|max:300',
        ]);

        try {
            DB::beginTransaction();

            $empresa->update($dados);

            DB::commit();

            return redirect()
                ->route('admin.empresas.show', $empresa)
                ->with('sucesso', 'Empresa atualizada com sucesso!');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar empresa', ['id' => $empresa->id, 'erro' => $e->getMessage()]);

            return back()
                ->withInput()
                ->withErrors(['geral' => 'Ocorreu um erro ao atualizar a empresa. Tente novamente.']);
        }
    }

    public function destroy(Empresa $empresa): RedirectResponse
    {
        try {
            // Verifica se há contratos vinculados antes de tentar excluir
            if ($empresa->contratos()->exists()) {
                return back()->withErrors([
                    'geral' => 'Não é possível excluir esta empresa pois ela possui contratos vinculados.',
                ]);
            }

            DB::beginTransaction();

            $empresa->delete();

            DB::commit();

            return redirect()
                ->route('admin.empresas.index')
                ->with('sucesso', 'Empresa excluída com sucesso.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Erro ao excluir empresa', ['id' => $empresa->id, 'erro' => $e->getMessage()]);

            return back()->withErrors(['geral' => 'Ocorreu um erro ao excluir a empresa. Tente novamente.']);
        }
    }
}
