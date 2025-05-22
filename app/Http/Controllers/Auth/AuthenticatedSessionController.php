<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * LOGIN DEL USUARIO --
     * LLAMAMOS AL LOGIN REQUEST PARA LA VALIDACION Y AL USUARIO AUTH
     * CREAMOS UN TOKEN PARA EL USUARIO AUTH_TOKEN Y DEVOLVEMOS LA RESPUESTA
     */
    public function store(LoginRequest $request): JsonResponse
    {

        $request->authenticate();

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user->currentAccessToken()->delete();
        return response()->json(['message' => 'Successfully logged out']);
    }
}
