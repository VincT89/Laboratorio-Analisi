<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DocumentTypeController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', DocumentType::class);

        $types = DocumentType::orderBy('sort_order')->orderBy('name')->get();

        return view('document_types.index', compact('types'));
    }

    public function create(): View
    {
        $this->authorize('create', DocumentType::class);

        return view('document_types.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', DocumentType::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:document_types,name'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ], [
            'name.unique' => 'Esiste già un tipo di documento con questo nome.',
        ]);

        DocumentType::create([
            'name' => $validated['name'],
            'is_active' => true,
            'sort_order' => $validated['sort_order']
                ?? ((int) DocumentType::max('sort_order') + 10),
        ]);

        return redirect()
            ->route('document-types.index')
            ->with('success', 'Tipo di documento creato correttamente.');
    }

    public function edit(DocumentType $documentType): View
    {
        $this->authorize('update', $documentType);

        return view('document_types.edit', compact('documentType'));
    }

    public function update(Request $request, DocumentType $documentType): RedirectResponse
    {
        $this->authorize('update', $documentType);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('document_types', 'name')->ignore($documentType),
            ],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ], [
            'name.unique' => 'Esiste già un tipo di documento con questo nome.',
        ]);

        $documentType->update([
            'name' => $validated['name'],
            'sort_order' => $validated['sort_order'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('document-types.index')
            ->with('success', 'Tipo di documento aggiornato correttamente.');
    }

    public function activate(DocumentType $documentType): RedirectResponse
    {
        $this->authorize('update', $documentType);
        $documentType->update(['is_active' => true]);

        return back()->with('success', 'Tipo di documento riattivato.');
    }

    public function deactivate(DocumentType $documentType): RedirectResponse
    {
        $this->authorize('update', $documentType);
        $documentType->update(['is_active' => false]);

        return back()->with('success', 'Tipo di documento disattivato.');
    }
}
