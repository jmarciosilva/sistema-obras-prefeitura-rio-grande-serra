<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ObraDetalheResource;
use App\Http\Resources\Api\V1\ObraResource;
use App\Models\Obra;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * API mobile — Obras (somente leitura).
 * KPIs vêm dos accessors do Model Obra (valor_contratado, valor_medido,
 * saldo_contratual, percentual_executado); por isso o eager load de
 * contratos.execucoes, evitando uma query por obra.
 */
class ObraController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'search'   => ['nullable', 'string', 'max:100'],
            'status'   => ['nullable', 'integer'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $obras = Obra::with(['status', 'contratos.execucoes'])
            ->when($request->filled('status'), fn($q) => $q->where('status_obra_id', $request->integer('status')))
            ->when($request->filled('search'), fn($q) => $q->where('descricao', 'like', '%' . $request->input('search') . '%'))
            ->latest()
            ->paginate($request->integer('per_page', 15))
            ->withQueryString();

        return ObraResource::collection($obras);
    }

    public function show(Request $request, Obra $obra): JsonResponse
    {
        $obra->load(['status', 'contratos.empresa', 'contratos.execucoes']);

        return response()->json(ObraDetalheResource::make($obra)->resolve($request));
    }
}
