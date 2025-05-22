<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class GoogleAuthController extends Controller
{
    public function redirectToAuth(): JsonResponse
    {
        return response()->json([
            'url' => Socialite::driver('google')
                ->stateless()
                ->redirect()
                ->getTargetUrl(),
        ]);
    }

    public function handleAuthCallback(Request $request): JsonResponse
    {
        try {
            /** @var \Laravel\Socialite\Contracts\User $googleUser */
            $googleUser = Socialite::driver('google')->stateless()->user();

            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'email_verified_at' => now(),
                ]
            );

            return response()->json([
                'user' => $user,
                'access_token' => $user->createToken('google-token')->plainTextToken,
                'token_type' => 'Bearer',
            ]);

        } catch (InvalidStateException $e) {
            return response()->json(['error' => 'Invalid session. Please try again.'], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Authentication error: ' . $e->getMessage()], 500);
        }
    }

    // Nuevo método para autenticar con token de Google
    public function handleAuthWithToken(Request $request): JsonResponse
    {
        try {
            $token = $request->input('token');

            if (!$token) {
                return response()->json(['error' => 'Google token is required'], 400);
            }

            // Verificar el token con Google
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->userFromToken($token);

            $user = User::firstOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName(),
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'email_verified_at' => now(),
                ]
            );

            return response()->json([
                'user' => $user,
                'access_token' => $user->createToken('google-token')->plainTextToken,
                'token_type' => 'Bearer',
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid Google token: ' . $e->getMessage()], 401);
        }
    }
}