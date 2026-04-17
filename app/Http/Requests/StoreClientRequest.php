<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    /**
     * Determina se l'utente è autorizzato a fare questa richiesta.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Client::class);
    }

    /**
     * Regole di validazione per la creazione di un cliente.
     */
    public function rules(): array
    {
        return [
            'company_name' => ['required', 'string', 'max:255'],
            'first_name'   => ['nullable', 'string', 'max:255'],
            'last_name'    => ['nullable', 'string', 'max:255'],
            'type'         => ['required', 'in:company,individual'],
            'email'        => ['nullable', 'email', 'max:255'],
            'phone'        => ['nullable', 'string', 'max:50'],
            'pec'          => ['nullable', 'email', 'max:255'],
            'address'      => ['nullable', 'string', 'max:255'],
            'city'         => ['nullable', 'string', 'max:100'],
            'province'     => ['nullable', 'string', 'max:5'],
            'postal_code'  => ['nullable', 'string', 'max:10'],
            'country'      => ['nullable', 'string', 'max:100'],
            'tax_code'     => ['nullable', 'string', 'max:20'],
            'vat_number'   => ['nullable', 'string', 'max:20'],
            'sdi_code'     => ['nullable', 'string', 'max:10'],
            'notes'        => ['nullable', 'string'],
        ];
    }

    /**
     * Messaggi di errore personalizzati.
     */
    public function messages(): array
    {
        return [
            'company_name.required' => 'La ragione sociale è obbligatoria.',
            'type.required'         => 'Il tipo cliente è obbligatorio.',
            'type.in'               => 'Il tipo cliente non è valido.',
            'email.email'           => 'Inserire un indirizzo email valido.',
            'pec.email'             => 'Inserire un indirizzo PEC valido.',
        ];
    }
}