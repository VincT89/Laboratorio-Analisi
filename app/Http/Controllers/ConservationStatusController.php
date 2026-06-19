<?php

namespace App\Http\Controllers;

use App\Models\ConservationStatus;
use Illuminate\Http\Request;

class ConservationStatusController extends Controller
{
    /**
     * Mostra la lista (solo admin).
     */
    public function index()
    {
        $this->authorize('viewAny', ConservationStatus::class);

        $statuses = ConservationStatus::orderBy('name')->get();
        return view('conservation_statuses.index', compact('statuses'));
    }

    /**
     * Mostra il form di creazione.
     */
    public function create()
    {
        $this->authorize('create', ConservationStatus::class);
        return view('conservation_statuses.create');
    }

    /**
     * Salva.
     */
    public function store(Request $request)
    {
        $this->authorize('create', ConservationStatus::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:conservation_statuses,name',
        ], [
            'name.unique' => 'Esiste già un\'unità di misura con questo nome.'
        ]);

        ConservationStatus::create([
            'name' => $validated['name'],
            'is_active' => true,
        ]);

        return redirect()->route('conservation-statuses.index')->with('success', 'Unità di misura creata correttamente.');
    }

    /**
     * Mostra il form di modifica.
     */
    public function edit(ConservationStatus $conservationStatus)
    {
        $this->authorize('update', $conservationStatus);
        return view('conservation_statuses.edit', compact('conservationStatus'));
    }

    /**
     * Aggiorna.
     */
    public function update(Request $request, ConservationStatus $conservationStatus)
    {
        $this->authorize('update', $conservationStatus);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:conservation_statuses,name,' . $conservationStatus->id,
            'is_active' => 'boolean',
        ], [
            'name.unique' => 'Esiste già un\'unità di misura con questo nome.'
        ]);
        
        $conservationStatus->update([
            'name' => $validated['name'],
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('conservation-statuses.index')->with('success', 'Unità di misura aggiornata correttamente.');
    }

    /**
     * Abilita (scorciatoia)
     */
    public function activate(ConservationStatus $conservationStatus)
    {
        $this->authorize('update', $conservationStatus);
        $conservationStatus->update(['is_active' => true]);
        return back()->with('success', 'Unità di misura riattivata.');
    }

    /**
     * Disabilita (scorciatoia)
     */
    public function deactivate(ConservationStatus $conservationStatus)
    {
        $this->authorize('update', $conservationStatus);
        $conservationStatus->update(['is_active' => false]);
        return back()->with('success', 'Unità di misura disattivata.');
    }
}
