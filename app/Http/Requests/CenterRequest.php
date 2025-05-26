<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CenterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'municipality' => ['nullable', 'string', 'max:255'],
            'locality' => ['required', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
            'number' => ['required', 'integer', 'min:1', 'max:100'],
            'phone' => ['required', 'digits:9'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del centro es obligatorio.',
            'name.string' => 'El nombre del centro debe ser una cadena de texto.',
            'name.max' => 'El nombre del centro no puede tener más de 255 caracteres.',

            'municipality.string' => 'El municipio debe ser una cadena de texto.',
            'municipality.max' => 'El municipio no puede tener más de 255 caracteres.',

            'locality.required' => 'La localidad es obligatoria.',
            'locality.string' => 'La localidad debe ser una cadena de texto.',
            'locality.max' => 'La localidad no puede tener más de 255 caracteres.',

            'street.required' => 'La calle es obligatoria.',
            'street.string' => 'La calle debe ser una cadena de texto.',
            'street.max' => 'La calle no puede tener más de 255 caracteres.',

            'number.required' => 'El número es obligatorio.',
            'number.integer' => 'El número debe ser un número entero.',
            'number.min' => 'El número debe ser mayor que cero.',
            'number.max' => 'El número no puede ser mayor que 100.',

            'phone.required' => 'El teléfono es obligatorio.',
            'phone.digits' => 'El teléfono debe tener exactamente 9 dígitos.',
        ];
    }
}
