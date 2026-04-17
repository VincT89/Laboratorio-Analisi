<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSampleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Sample::class);
    }

    public function rules(): array
    {
        $rules = [
            'creation_mode'   => ['required', 'in:standard,sensitive'],
            'collected_at'    => ['required', 'date'],
        ];

        if ($this->input('creation_mode') === 'sensitive') {
            $rules['sample_type_id'] = [
                'required', 
                \Illuminate\Validation\Rule::exists('sample_types', 'id')
                    ->where('is_active', 1)
                    ->where('is_sensitive', 1)
            ];
            $rules['collection_site'] = ['required', 'string', 'max:255'];
            $rules['collected_by']    = ['required', 'string', 'max:255'];
        } else {
            $rules['client_id']       = ['required', 'exists:clients,id'];
            $rules['sample_type_id']  = [
                'required', 
                \Illuminate\Validation\Rule::exists('sample_types', 'id')
                    ->where('is_active', 1)
                    ->where('is_sensitive', 0)
            ];
            $rules['collection_site'] = ['required', 'string', 'max:255'];
            $rules['collected_by']    = ['required', 'string', 'max:255'];
            $rules['notes']           = ['nullable', 'string'];
        }

        return $rules;
    }

    public function messages(): array
    {
        $messages = [
            'creation_mode.required'   => 'Modalità di creazione mancante.',
            'creation_mode.in'         => 'Modalità di creazione non valida.',
            'collected_at.required'    => 'La data di prelievo è obbligatoria.',
            'collected_at.date'        => 'La data di prelievo non è valida.',
            'sample_type_id.required'  => 'Il tipo campione è obbligatorio.',
            'sample_type_id.exists'    => 'Il tipo campione selezionato non è valido o incompatibile con la modalità scelta.',
            'collection_site.required' => 'Il luogo di prelievo è obbligatorio.',
            'collected_by.required'    => 'Il nome del prelevatore è obbligatorio.',
        ];

        if ($this->input('creation_mode') === 'sensitive') {
            $messages['collection_site.required'] = 'Il luogo di prelievo è richiesto nella preregistrazione tecnica.';
            $messages['collected_by.required']    = 'Il nome del prelevatore è richiesto nella preregistrazione tecnica.';
        } else {
            $messages['client_id.required']       = 'Il cliente è obbligatorio nel flusso standard.';
            $messages['client_id.exists']         = 'Il cliente selezionato non esiste.';
        }

        return $messages;
    }
}