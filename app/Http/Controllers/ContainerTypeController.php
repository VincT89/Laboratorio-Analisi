<?php

namespace App\Http\Controllers;

use App\Models\ContainerType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContainerTypeController extends Controller
{
    /**
     * Mostra la lista dei tipi contenitore (solo admin).
     */
    public function index()
    {
        $this->authorize('viewAny', ContainerType::class);

        $types = ContainerType::orderBy('name')->get();
        return view('container_types.index', compact('types'));
    }

    /**
     * Mostra il form di creazione.
     */
    public function create()
    {
        $this->authorize('create', ContainerType::class);
        return view('container_types.create');
    }

    /**
     * Salva il nuovo tipo contenitore.
     */
    public function store(Request $request)
    {
        $this->authorize('create', ContainerType::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:container_types,name',
        ], [
            'name.unique' => 'Esiste già un tipo di contenitore con questo nome.'
        ]);

        ContainerType::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'is_active' => true,
        ]);

        return redirect()->route('container-types.index')->with('success', 'Tipo di contenitore creato correttamente.');
    }

    /**
     * Mostra il form di modifica.
     */
    public function edit(ContainerType $containerType)
    {
        $this->authorize('update', $containerType);
        return view('container_types.edit', compact('containerType'));
    }

    /**
     * Aggiorna un tipo contenitore esistente.
     */
    public function update(Request $request, ContainerType $containerType)
    {
        $this->authorize('update', $containerType);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:container_types,name,' . $containerType->id,
            'is_active' => 'boolean',
        ], [
            'name.unique' => 'Esiste già un tipo di contenitore con questo nome.'
        ]);
        
        $containerType->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('container-types.index')->with('success', 'Tipo di contenitore aggiornato correttamente.');
    }

    /**
     * Abilita un tipo (scorciatoia)
     */
    public function activate(ContainerType $containerType)
    {
        $this->authorize('update', $containerType);
        $containerType->update(['is_active' => true]);
        return back()->with('success', 'Tipo di contenitore riattivato.');
    }

    /**
     * Disabilita un tipo (scorciatoia)
     */
    public function deactivate(ContainerType $containerType)
    {
        $this->authorize('update', $containerType);
        $containerType->update(['is_active' => false]);
        return back()->with('success', 'Tipo di contenitore disattivato.');
    }
}
