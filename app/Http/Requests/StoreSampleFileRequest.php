<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSampleFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\SampleFile::class);
    }

    public function rules(): array
    {
        return [
            'file'        => ['required', 'file', 'max:20480', 'mimes:pdf,doc,docx,xls,xlsx,csv,jpg,jpeg,png'],
            'type'        => ['required', 'in:report,attachment,prescription,revised_report'],
            'description' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'Il file è obbligatorio.',
            'file.file'     => 'Il file caricato non è valido.',
            'file.max'      => 'Il file non può superare i 20MB.',
            'file.mimes'    => 'Sono accettati solo file PDF, Word, Excel, CSV e immagini.',
            'type.required' => 'Il tipo di documento è obbligatorio.',
            'type.in'       => 'Tipo di documento non valido.',
        ];
    }
}