<?php

namespace App\Http\Requests;

use Orion\Http\Requests\Request;

class PersonRequest extends Request
{
    public function generalRules(): array
    {
        return [
            'identification_type' => 'required|in:DNI,NIF',
            'identification_number' => 'required|string|size:9',
            'name' => 'required|string|max:255',
            'first_surname' => 'required|string|max:255',
            'second_surname' => 'nullable|string|max:255',
            'observations' => 'nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'identification_type.required' => 'El tipo de identificación es requerido.',
            'identification_type.in' => 'El tipo de identificación debe ser DNI o NIF .',
            'identification_number.required' => 'El número de identificación es requerido.',
            'identification_number.string' => 'El número de identificación debe ser un string.',
            'identification_number.size' => 'El número de identificación debe tener 9 caracteres.',
            'name.required' => 'El nombre es requerido.',
            'name.string' => 'El nombre debe ser un string.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'first_surname.required' => 'El primer apellido es requerido.',
            'first_surname.string' => 'El primer apellido debe ser un string.',
            'first_surname.max' => 'El primer apellido no puede tener más de 255 caracteres .',
            'second_surname.string' => 'El segundo apellido debe ser un string.',
            'second_surname.max' => 'El segundo apellido no puede tener más de 255 caracteres .',
            'observations.string' => 'Las observaciones deben ser un string.',
            'observations.max' => 'Las observaciones no pueden tener más de 255 caracteres .',
        ];
    }
}
