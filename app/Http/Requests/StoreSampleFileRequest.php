<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSampleFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', SampleFile::class);
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,csv,jpg,jpeg,png'],
            'document_type_id' => [
                'required',
                Rule::exists('document_types', 'id')->where('is_active', true),
            ],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Il file è obbligatorio.',
            'file.file' => 'Il file caricato non è valido.',
            'file.max' => 'Il file non può superare i 20MB.',
            'file.mimes' => 'Sono accettati solo file PDF, Word, Excel, CSV e immagini.',
            'document_type_id.required' => 'Il tipo di documento è obbligatorio.',
            'document_type_id.exists' => 'Il tipo di documento selezionato non è disponibile.',
        ];
    }
}
