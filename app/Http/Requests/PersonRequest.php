<?php

namespace App\Http\Requests;

// use Illuminate\Validation\Rule;
// use Orion\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PersonRequest extends FormRequest
{
    public function authorize()
    {
        $user = $this->user();

        if (!$user) {
            return false;
        }
        return $user->hasRole('superAdmin') || $user->hasRole('visor');
    }

    public function rules()
    {
        $Id = $this->route('id');

        return [
            'identification_type' => 'required|in:DNI,NIF',
            'identification_number' => [
                'required',
                'string',
                'size:9',
                Rule::unique('people')->ignore($Id)
            ],
            'name' => 'required|string|max:255',
            'first_surname' => 'required|string|max:255',
            'second_surname' => 'nullable|string|max:255',
            'observations' => 'nullable|string|max:255',

            'client' => 'nullable|array',
            'client.agent' => 'nullable|string|max:255',

            'collegiate' => 'nullable|array',
            'collegiate.birth_date' => 'nullable|date|before_or_equal:today',
            'collegiate.nationality' => 'nullable|string|max:100',
            'collegiate.banking_entity' => 'nullable|string|max:100',
            'collegiate.account_number' => 'nullable|string|size:24|regex:/^[A-Z0-9]{24}$/',
            'collegiate.college' => 'nullable|string|max:100',
            'collegiate.origin_college' => 'nullable|string|max:100',
            'collegiate.origin_college_number' => 'nullable|string|max:50',
            'collegiate.degree' => 'nullable|in:Technical Architect',
            'collegiate.collegiate_number' => 'nullable|string|max:50',
            'collegiate.specialty' => 'nullable|string|max:100',
            'collegiate.termination_date' => 'nullable|date|after:graduation_date',
            'collegiate.graduation_date' => 'nullable|date|before_or_equal:today',
            'collegiate.career_end_et' => 'nullable|string|max:100',
            'collegiate.web_page' => 'nullable|url|max:255',
            'collegiate.council_reg_number' => 'nullable|string|max:50',
            'collegiate.situation' => 'nullable|string|max:100',

            'address' => 'nullable|array',
            'address.*.country' => 'nullable|string|max:100',
            'address.*.province' => 'nullable|string|max:100',
            'address.*.municipality' => 'nullable|string|max:100',
            'address.*.locality' => 'nullable|string|max:100',
            'address.*.street' => 'nullable|string|max:255',
            'address.*.number' => 'nullable|integer|min:1|max:100',
            'address.*.postal_code' => 'nullable|string|size:5',

            'email' => 'nullable|array',
            'email.*.email' => [
                'required_with:email',
                'email',
                'max:255',
                Rule::unique('emails')->where(fn($query) => $query->where('person_id', '!=', $Id))
            ],

            'phone' => 'nullable|array',
            'phone.*.phone' => [
                'required_with:phone',
                'string',
                'size:9',
                'regex:/^[0-9]{9}$/',
                Rule::unique('phones')->where(fn($query) => $query->where('person_id', '!=', $Id))
            ]

        ];
    }

    public function messages()
    {
        return [
            'identification_type.required' => 'El tipo de identificación es requerido.',
            'identification_type.in' => 'El tipo de identificación debe ser DNI o NIF.',
            'identification_number.required' => 'El número de identificación es requerido.',
            'identification_number.string' => 'El número de identificación debe ser un string.',
            'identification_number.size' => 'El número de identificación debe tener 9 caracteres.',
            'identification_number.unique' => 'El número de identificación ya existe.',
            'name.required' => 'El nombre es requerido.',
            'name.string' => 'El nombre debe ser un string.',
            'name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'first_surname.required' => 'El primer apellido es requerido.',
            'first_surname.string' => 'El primer apellido debe ser un string.',
            'first_surname.max' => 'El primer apellido no puede tener más de 255 caracteres.',
            'second_surname.string' => 'El segundo apellido debe ser un string.',
            'second_surname.max' => 'El segundo apellido no puede tener más de 255 caracteres.',
            'observations.string' => 'Las observaciones deben ser un string.',
            'observations.max' => 'Las observaciones no pueden tener más de 255 caracteres.',

            'collegiate.birth_date.before_or_equal' => 'La fecha de nacimiento no puede ser futura.',
            'collegiate.termination_date.after' => 'La fecha de baja debe ser posterior a la de graduación.',
            'collegiate.graduation_date.before_or_equal' => 'La fecha de graduación no puede ser futura.',
            'collegiate.account_number.size' => 'El número de cuenta debe tener 24 caracteres.',
            'collegiate.account_number.regex' => 'El número de cuenta solo debe contener letras mayúsculas y números.',
            'collegiate.collegiate_number.unique' => 'Este número de colegiado ya existe.',
            'collegiate.degree.in' => 'El título debe ser "Technical Architect".',
            'collegiate.web_page.url' => 'La página web debe ser una URL válida.',

            'email.*.email.required_with' => 'El email es requerido',
            'email.*.email.email' => 'El formato del email no es válido',
            'email.*.email.max' => 'El email no debe exceder 255 caracteres',
            'email.*.email.unique' => 'El email ya existe',

            'address.*.number.min' => 'El número de la calle debe ser mayor a 0',
            'address.*.number.max' => 'El número de la calle no puede ser mayor a 100',
            'address.*.postal_code.size' => 'El código postal debe tener exactamente 5 caracteres',

            'phone.*.phone.required_with' => 'El teléfono es requerido',
            'phone.*.phone.size' => 'El teléfono debe tener exactamente 9 dígitos',
            'phone.*.phone.regex' => 'El teléfono solo debe contener números',
            'phone.*.phone.unique' => 'El teléfono ya existe',
        ];
    }

    public function attributes()
    {
        return [

            'client.agent' => 'agente',

            'email.*.email' => 'email',

            'phone.*.phone' => 'teléfono',

            'address.*.country' => 'país',
            'address.*.province' => 'provincia',
            'address.*.municipality' => 'municipio',
            'address.*.locality' => 'localidad',
            'address.*.street' => 'calle',
            'address.*.number' => 'número',
            'address.*.postal_code' => 'código postal',

            'collegiate.collegiate_number' => 'número de colegiado',
            'collegiate.degree' => 'título',
            'collegiate.web_page' => 'página web',
            'collegiate.account_number' => 'número de cuenta',
            'collegiate.birth_date' => 'fecha de nacimiento',
            'collegiate.termination_date' => 'fecha de baja',
            'collegiate.graduation_date' => 'fecha de graduación',
            'collegiate.observations' => 'observaciones',

        ];
    }
}