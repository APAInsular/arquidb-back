<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExpedientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'number' => 'required|string|size:10',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string|max:255',
            'site' => 'required|string|max:255',
            'postal_code' => 'nullable|string|size:5',
            'budget' => 'nullable|numeric|between:0,9999999.99',
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'El título es obligatorio.',
            'title.string' => 'El título debe ser una cadena de texto.',
            'title.max' => 'El título no debe superar los 255 caracteres.',

            'number.required' => 'El número es obligatorio.',
            'number.string' => 'El número debe ser una cadena de texto.',
            'number.size' => 'El número debe tener exactamente 10 caracteres.',
            'number.unique' => 'El número ya está en uso. Debe ser único.',

            'start_date.required' => 'La fecha de inicio es obligatoria.',
            'start_date.date' => 'La fecha de inicio debe ser una fecha válida.',

            'end_date.date' => 'La fecha de finalización debe ser una fecha válida.',
            'end_date.after_or_equal' => 'La fecha de finalización debe ser posterior o igual a la fecha de inicio.',

            'description.string' => 'La descripción debe ser una cadena de texto.',
            'description.max' => 'La descripción no debe superar los 255 caracteres.',

            'site.required' => 'El sitio es obligatorio.',
            'site.string' => 'El sitio debe ser una cadena de texto.',
            'site.max' => 'El sitio no debe superar los 255 caracteres.',

            'postal_code.string' => 'El código postal debe ser una cadena de texto.',
            'postal_code.size' => 'El código postal debe tener exactamente 5 caracteres.',

            'budget.numeric' => 'El presupuesto debe ser un valor numérico.',
            'budget.between' => 'El presupuesto debe estar entre 0 y 9,999,999.99.',
        ];
    }
}
