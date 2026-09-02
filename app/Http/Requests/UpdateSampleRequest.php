<?php

namespace App\Http\Requests;

use App\Models\Sample;
use App\Models\SampleType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validazione dei dati modificabili del campione.
 * Lo stato è modificabile dal form soltanto per i campioni standard.
 */
class UpdateSampleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('sample'));
    }

    protected function prepareForValidation(): void
    {
        if (! $this->user()->isAdmin() && $this->has('code_progressive')) {
            $this->request->remove('code_progressive');
        }
    }

    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'collected_at' => ['required', 'date'],
            'sample_type_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $type = SampleType::find($value);
                    if (! $type) {
                        $fail('Il tipo campione selezionato non esiste.');
                    } elseif (! $type->is_active && $value != $this->route('sample')->sample_type_id) {
                        $fail('Questo tipo campione è disattivato e non può essere assegnato a nuovi record.');
                    }
                },
            ],
            'collection_site' => ['nullable', 'string', 'max:255'],
            'collected_by' => ['required', 'string', 'max:255'],
            'status' => [
                'sometimes',
                Rule::prohibitedIf(fn () => $this->route('sample')->isSensitive()),
                'required',
                Rule::in(array_keys(Sample::STATUS_LABELS)),
            ],
            'notes' => ['nullable', 'string'],
            'lab_archived_by_name' => ['nullable', 'string', 'max:255'],
            'container_type_id' => ['nullable', 'exists:container_types,id'],
            'conservation_status' => [
                'nullable', 
                \Illuminate\Validation\Rule::exists('conservation_statuses', 'name')->where('is_active', true)
            ],
            'sample_quantity' => ['nullable', 'numeric', 'min:0'],
            'sample_quantity_unit' => [
                'nullable', 
                \Illuminate\Validation\Rule::exists('measurement_units', 'name')->where('is_active', true)
            ],
            'code_progressive' => [
                'nullable',
                'integer',
                'min:1',
                'max:9999',
                Rule::unique('samples')->where(function ($query) {
                    return $query->where('code_year', $this->route('sample')->code_year);
                })->ignore($this->route('sample')->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required' => 'Il cliente è obbligatorio.',
            'client_id.exists' => 'Il cliente selezionato non esiste.',
            'collected_at.required' => 'La data di prelievo è obbligatoria.',
            'collected_at.date' => 'La data di prelievo non è valida.',
            'sample_type_id.required' => 'Il tipo campione è obbligatorio.',
            'collected_by.required' => 'Il nome del prelevatore è obbligatorio.',
            'status.required' => 'Lo stato del campione è obbligatorio.',
            'status.in' => 'Lo stato del campione selezionato non è valido.',
            'status.prohibited' => 'Lo stato dei campioni sensibili deve essere gestito tramite il flusso dedicato.',
            'code_progressive.unique' => 'Il progressivo specificato è già in uso per l\'anno del campione in oggetto.',
        ];
    }
}
