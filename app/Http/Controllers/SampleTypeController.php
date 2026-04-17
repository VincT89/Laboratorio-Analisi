<?php

namespace App\Http\Controllers;

use App\Models\SampleType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SampleTypeController extends Controller
{
    /**
     * Mostra la lista dei tipi (solo admin).
     */
    public function index()
    {
        // Questo controller è per Admin, verifichiamo il ruolo tramite Policy o Gate se presente
        // oppure nel construct
        $this->authorize('viewAny', SampleType::class);

        $types = SampleType::orderBy('name')->get();
        return view('sample_types.index', compact('types'));
    }

    /**
     * Mostra il form di creazione.
     */
    public function create()
    {
        $this->authorize('create', SampleType::class);
        return view('sample_types.create');
    }

    /**
     * Salva il nuovo tipo.
     */
    public function store(Request $request)
    {
        $this->authorize('create', SampleType::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:sample_types,name',
            'is_sensitive' => 'boolean'
        ], [
            'name.unique' => 'Esiste già un tipo di campione con questo nome.'
        ]);

        SampleType::create([
            'name' => $validated['name'],
            'is_active' => true,
            'is_sensitive' => $request->boolean('is_sensitive')
        ]);

        return redirect()->route('sample-types.index')->with('success', 'Tipo creato correttamente.');
    }

    /**
     * Mostra il form di modifica.
     */
    public function edit(SampleType $sampleType)
    {
        $this->authorize('update', $sampleType);
        return view('sample_types.edit', compact('sampleType'));
    }

    /**
     * Aggiorna un tipo esistente (es. ridenominazione o attivazione/disattivazione).
     */
    public function update(Request $request, SampleType $sampleType)
    {
        $this->authorize('update', $sampleType);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:sample_types,name,' . $sampleType->id,
            'is_active' => 'boolean',
            'is_sensitive' => 'boolean'
        ], [
            'name.unique' => 'Esiste già un tipo di campione con questo nome.'
        ]);
        
        $sampleType->update([
            'name' => $validated['name'],
            'is_active' => $request->has('is_active'),
            'is_sensitive' => $request->boolean('is_sensitive')
        ]);

        return redirect()->route('sample-types.index')->with('success', 'Tipo aggiornato correttamente.');
    }

    /**
     * Abilita un tipo (scorciatoia)
     */
    public function activate(SampleType $sampleType)
    {
        $this->authorize('update', $sampleType);
        $sampleType->update(['is_active' => true]);
        return back()->with('success', 'Tipo di campione riattivato.');
    }

    /**
     * Disabilita un tipo (scorciatoia)
     */
    public function deactivate(SampleType $sampleType)
    {
        $this->authorize('update', $sampleType);
        $sampleType->update(['is_active' => false]);
        return back()->with('success', 'Tipo di campione disattivato.');
    }
}
