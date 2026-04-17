<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStaffRequest extends FormRequest
{
    /**
     * Solo gli admin possono modificare utenti staff.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        $staffId = $this->route('staff')->id;

        return [
            'name'     => 'required|string|max:255',
            'email'    => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($staffId)],
            'password' => 'nullable|string|min:8|confirmed',
            'role'     => 'required|exists:roles,name',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'      => 'Il nome è obbligatorio.',
            'email.required'     => 'L\'email è obbligatoria.',
            'email.unique'       => 'Questa email è già in uso.',
            'password.min'       => 'La password deve essere di almeno 8 caratteri.',
            'password.confirmed' => 'Le password non coincidono.',
            'role.required'      => 'Il ruolo è obbligatorio.',
            'role.exists'        => 'Il ruolo selezionato non è valido.',
        ];
    }
}