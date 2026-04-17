<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStaffRequest extends FormRequest
{
    /**
     * Solo gli admin possono creare utenti staff.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'required|exists:roles,name',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'     => 'Il nome è obbligatorio.',
            'email.required'    => 'L\'email è obbligatoria.',
            'email.unique'      => 'Questa email è già in uso.',
            'password.required' => 'La password è obbligatoria.',
            'password.min'      => 'La password deve essere di almeno 8 caratteri.',
            'password.confirmed'=> 'Le password non coincidono.',
            'role.required'     => 'Il ruolo è obbligatorio.',
            'role.exists'       => 'Il ruolo selezionato non è valido.',
        ];
    }
}