<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ContratoDetalheResource;
use App\Http\Resources\Api\V1\ContratoResource;
use App\Models\Contrato;
use App\Services\DashboardObrasService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

/**
 * API mobile — Contratos (somente leitura).
 * Busca igual à do ContratoController web (número, obra, empresa).
 */
class ContratoController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'search'   => ['nullable', 'string', 'max:100'],
            'situacao' => ['nullable', Rule::in(ContratoResource::SITUACOES)],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $busca = $request->input('search');

        $contratos = Contrato::with(['obra', 'empresa'])
            ->withSum('execucoes', 'valor_medido')
            // Agrupado para não "vazar" o OR sobre o filtro de situação
            ->when($request->filled('search'), fn($q) => $q->where(
                fn($q) => $q->where('numero_contrato_ano', 'like', "%{$busca}%")
                    ->orWhereHas('obra', fn($q) => $q->where('descricao', 'like', "%{$busca}%"))
                    ->orWhereHas('empresa', fn($q) => $q->where('razao_social', 'like', "%{$busca}%")
                        ->orWhere('nome_fantasia', 'like', "%{$busca}%"))
            ))
            ->when($request->filled('situacao'), fn($q) => $this->filtrarSituacao($q, $request->input('situacao')))
            ->latest()
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return ContratoResource::collection($contratos);
    }

    public function show(Request $request, Contrato $contrato): JsonResponse
    {
        $contrato->load(['obra.status', 'empresa', 'execucoes'])
            ->loadSum('execucoes', 'valor_medido');

        return response()->json(ContratoDetalheResource::make($contrato)->resolve($request));
    }

    /**
     * Mesmos limites de ContratoResource::situacaoVigencia() e do dashboard.
     */
    private function filtrarSituacao($query, string $situacao): void
    {
        $limite = now()->addDays(DashboardObrasService::DIAS_CONTRATO_VENCENDO);

        match ($situacao) {
            ContratoResource::SITUACAO_SEM_VIGENCIA   => $query->whereNull('vigencia_contrato'),
            ContratoResource::SITUACAO_VENCIDO        => $query->whereNotNull('vigencia_contrato')
                ->where('vigencia_contrato', '<', now()),
            ContratoResource::SITUACAO_VENCE_EM_BREVE => $query->whereBetween('vigencia_contrato', [now(), $limite]),
            ContratoResource::SITUACAO_VIGENTE        => $query->where('vigencia_contrato', '>', $limite),
        };
    }
}
