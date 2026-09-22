<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\DashboardObrasService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __invoke(DashboardObrasService $dashboard): JsonResponse
    {
        return response()->json([
            'obras'         => $dashboard->resumoObras(),
            'contratos'     => $dashboard->resumoContratos(),
            'status'        => $dashboard->obrasPorStatus(),
            'alertas'       => $dashboard->alertas(),
            'atualizado_em' => now()->toIso8601String(),
        ]);
    }
}
