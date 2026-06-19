<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSampleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Sample::class);
    }

    protected function prepareForValidation(): void
    {
        if (!$this->user()->isAdmin() && $this->has('code_progressive')) {
            $this->request->remove('code_progressive');
        }
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
            $rules['collection_site'] = ['nullable', 'string', 'max:255'];
            $rules['collected_by']    = ['required', 'string', 'max:255'];
        } else {
            $rules['client_id']       = ['required', 'exists:clients,id'];
            $rules['sample_type_id']  = [
                'required', 
                \Illuminate\Validation\Rule::exists('sample_types', 'id')
                    ->where('is_active', 1)
                    ->where('is_sensitive', 0)
            ];
            $rules['collection_site'] = ['nullable', 'string', 'max:255'];
            $rules['collected_by']    = ['required', 'string', 'max:255'];
            $rules['notes']           = ['nullable', 'string'];
        }

        $rules['lab_archived_by_name'] = ['nullable', 'string', 'max:255'];
        $rules['container_type_id']    = ['nullable', 'exists:container_types,id'];
        $rules['conservation_status']  = [
            'nullable', 
            \Illuminate\Validation\Rule::exists('conservation_statuses', 'name')->where('is_active', true)
        ];
        $rules['sample_quantity']      = ['nullable', 'numeric', 'min:0'];
        $rules['sample_quantity_unit'] = [
            'nullable', 
            \Illuminate\Validation\Rule::exists('measurement_units', 'name')->where('is_active', true)
        ];
        $year = (int) now()->format('y');
        $rules['code_progressive'] = [
            'nullable', 
            'integer', 
            'min:1', 
            'max:9999',
            \Illuminate\Validation\Rule::unique('samples')->where(function ($query) use ($year) {
                return $query->where('code_year', $year);
            })
        ];
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
            'collected_by.required'    => 'Il nome del prelevatore è obbligatorio.',
        ];

        if ($this->input('creation_mode') === 'sensitive') {
            $messages['collected_by.required']    = 'Il nome del prelevatore è richiesto nella preregistrazione tecnica.';
        } else {
            $messages['client_id.required']       = 'Il cliente è obbligatorio nel flusso standard.';
            $messages['client_id.exists']         = 'Il cliente selezionato non esiste.';
        }

        $messages['code_progressive.unique'] = 'Il progressivo specificato è già in uso per l\'anno in corso.';

        return $messages;
    }
}