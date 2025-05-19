<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function show(Request $request)
    {
        return $request->user();
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'string|max:255',
            'email' => 'email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update($request->only('name', 'email'));

        return response()->json([
            'message' => 'Perfil actualizado correctamente.',
            'user' => $user
        ]);
    }

    public function destroy(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'password' => 'required|min:8',
        ]);

        if (!Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['La contraseña es incorrecta.'],
            ]);
        }

        $user->tokens()->delete();
        $user->delete();

        return response()->json([
            'message' => 'Cuenta eliminada correctamente.'
        ]);
    }
}
