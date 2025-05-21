<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class UserController extends Controller

    /**
     * VER USUARIO --
     * DEVOLVEMOS AL USUARIO 
     */
{
    public function show(Request $request)
    {
        return $request->user();
    }

    /**
     * ACTUAIZA EL USUARIO --
     * LLAMAMOS AL USUARIO AUTH 
     * VALIDAMOS CAMPOS, HACEMOS EL UPDATE DEL USER Y LE PASAMOS LOS ATRIBUTOS DEL REQUEST 
     * Y UN RETURN CON EL USUARIO ACTUALIZADO
     */

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

    /**
     * BORRAR EL USUARIO --
     * LLAMAMOS AL USUARIO AUTH 
     * TENEMOS UNA VALIDACION DE LA CONTRASEÑA Y COMPROBAMOS SI ES LA MISMA QUE LA DEL USER 
     * BORRAMOS EL TOKEN DEL USUARIO Y EL USUARIO Y LA RESPUESTA 
     */

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

    /**
     * CAMBIAR LA CONTRASEÑA DEL USUARIO --
     * LLAMAMOS AL USUARIO AUTH 
     * TENEMOS UNA VALIDACION DE LA CONTRASEÑA ANTERIOR DE LA NUEVA Y LA CONFIRMACION
     * Y COMPROBAMOS QUE EL PASSWORD BEFORE SEA LA MISMA QUE LA DEL USER 
     * LA PASSWORD DEL USER LE METEMOS EL NUEVO PASSWORD ENCRIPADO, GUARDAMOS EL USUARIO Y LA RESPUESTA
     */

    public function changePassword(Request $request)
    {

        $user = auth()->user();
        $data = $request->validate(
            [
                'passwordBefore' => 'required|min:8',
                'password' => 'required|min:8',
                'password_confirmation' => 'required|same:password',
            ],
            [
                'passwordBefore.required' => 'Debes ingresar la contraseña anterior.',
                'password.required' => 'Debes ingresar la nueva contraseña.',
                'password.min' => 'Debes ingresar una contraseña de más de 8 caracteres.',
                'password_confirmation.required' => 'Debes ingresar la confirmación de la contraseña.',
                'password_confirmation.same' => 'La contraseña y la confirmación deben ser iguales.',
            ]
        );

        if (!Hash::check($request->passwordBefore, $user->password)) {
            throw ValidationException::withMessages([
                'passwordBefore' => ['La contraseña es incorrecta.'],
            ]);
        }

        $user->password = bcrypt($data['password']);
        $user->save();
        return response()->json(['message' => 'Contraseña actualizada correctamente.']);
    }
}
