<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * API mobile — autenticação por token (Sanctum).
 * Usa os mesmos usuários da aplicação web; não altera o login do Breeze.
 */
class AuthController extends Controller
{
    /** Validade do token emitido para o app. */
    private const VALIDADE_TOKEN_DIAS = 30;

    public function login(Request $request): JsonResponse
    {
        $dados = $request->validate([
            'email'       => ['required', 'string', 'email'],
            'password'    => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $usuario = User::where('email', $dados['email'])->first();

        if (! $usuario || ! Hash::check($dados['password'], $usuario->password)) {
            return response()->json(['message' => 'Credenciais inválidas.'], 401);
        }

        if (! $usuario->ativo) {
            return response()->json([
                'message' => 'Sua conta está inativa. Entre em contato com o administrador.',
            ], 403);
        }

        $token = $usuario->createToken(
            'mobile:' . ($dados['device_name'] ?? 'app'),
            ['*'],
            now()->addDays(self::VALIDADE_TOKEN_DIAS)
        );

        return response()->json([
            'token'      => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $token->accessToken->expires_at?->toIso8601String(),
            'user'       => new UserResource($usuario),
        ]);
    }

    /** Revoga apenas o token usado nesta requisição. */
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout realizado com sucesso.']);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json(UserResource::make($request->user())->resolve($request));
    }
}
