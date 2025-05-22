<?php

namespace App\Http\Requests;

use Orion\Http\Requests\Request;
use Illuminate\Foundation\Http\FormRequest;

class CollegiateRequest extends FormRequest
{

    public function authorize()
    {
        $user = $this->user();

        if (!$user) {
            return false;
        }
        return $user->hasRole('superAdmin') || $user->hasRole('visor');
    }


    public function rules(): array
    {
        return [
            'birth_date' => 'required|date',
            'nationality' => 'nullable|string|max:255',
            'banking_entity' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:24',
            'college' => 'nullable|string|max:255',
            'origin_college' => 'nullable|string|max:255',
            'origin_college_number' => 'nullable|string|max:255',
            'degree' => 'nullable|in:Technical Architect',
            'collegiate_number' => 'nullable|string|max:255',
            'specialty' => 'nullable|string|max:255',
            'termination_date' => 'nullable|date',
            'graduation_date' => 'nullable|date',
            'career_end_et' => 'nullable|string|max:255',
            'web_page' => 'nullable|string|max:255',
            'council_reg_number' => 'nullable|string|max:255',
            'situation' => 'nullable|string|max:255',
        ];
    }
}
