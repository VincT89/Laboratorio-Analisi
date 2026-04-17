<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validazione dei dati modificabili del campione.
 * Le transizioni di stato (accepted_at, status) sono gestite separatamente dalle Action del Controller.
 */
class UpdateSampleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('sample'));
    }

    public function rules(): array
    {
        return [
            'client_id'       => ['required', 'exists:clients,id'],
            'collected_at'    => ['required', 'date'],
            'sample_type_id'  => [
                'required',
                function($attribute, $value, $fail) {
                    $type = \App\Models\SampleType::find($value);
                    if (!$type) {
                        $fail('Il tipo campione selezionato non esiste.');
                    } elseif (!$type->is_active && $value != $this->route('sample')->sample_type_id) {
                        $fail('Questo tipo campione è disattivato e non può essere assegnato a nuovi record.');
                    }
                }
            ],
            'collection_site' => ['required', 'string', 'max:255'],
            'collected_by'    => ['required', 'string', 'max:255'],
            'notes'           => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required'          => 'Il cliente è obbligatorio.',
            'client_id.exists'            => 'Il cliente selezionato non esiste.',
            'collected_at.required'       => 'La data di prelievo è obbligatoria.',
            'collected_at.date'           => 'La data di prelievo non è valida.',
            'sample_type_id.required'     => 'Il tipo campione è obbligatorio.',
            'collection_site.required'    => 'Il luogo di prelievo è obbligatorio.',
            'collected_by.required'       => 'Il nome del prelevatore è obbligatorio.',
        ];
    }
}