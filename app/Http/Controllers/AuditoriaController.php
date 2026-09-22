<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\User;
use App\Services\AuditoriaFormatador;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Histórico de Atividades — SOMENTE LEITURA.
 * Acesso: Administrador e Secretário (middleware na rota + Gate aqui).
 */
class AuditoriaController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('ver-auditoria');

        $filtros = $request->validate([
            'usuario' => ['nullable', 'integer'],
            'de'      => ['nullable', 'date_format:Y-m-d'],
            'ate'     => ['nullable', 'date_format:Y-m-d'],
            'modulo'  => ['nullable', 'in:' . implode(',', array_keys(Auditoria::MODULOS))],
            'acao'    => ['nullable', 'in:' . implode(',', array_keys(Auditoria::ACOES))],
        ]);

        $auditorias = Auditoria::with('usuario:id,name')
            ->filtrar($filtros)
            ->latest()
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $usuarios = User::orderBy('name')->get(['id', 'name']);

        return view('auditoria.index', compact('auditorias', 'usuarios', 'filtros'));
    }

    public function show(Auditoria $auditoria, AuditoriaFormatador $formatador): View
    {
        Gate::authorize('ver-auditoria');

        $auditoria->load('usuario:id,name,email');
        $linhas = $formatador->linhas($auditoria);

        return view('auditoria.show', compact('auditoria', 'linhas'));
    }
}
