<?php

namespace App\Http\Controllers;

use App\Models\MeasurementUnit;
use Illuminate\Http\Request;

class MeasurementUnitController extends Controller
{
    /**
     * Mostra la lista (solo admin).
     */
    public function index()
    {
        $this->authorize('viewAny', MeasurementUnit::class);

        $units = MeasurementUnit::orderBy('name')->get();
        return view('measurement_units.index', compact('units'));
    }

    /**
     * Mostra il form di creazione.
     */
    public function create()
    {
        $this->authorize('create', MeasurementUnit::class);
        return view('measurement_units.create');
    }

    /**
     * Salva.
     */
    public function store(Request $request)
    {
        $this->authorize('create', MeasurementUnit::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:measurement_units,name',
        ], [
            'name.unique' => 'Esiste già un\'unità di misura con questo nome.'
        ]);

        MeasurementUnit::create([
            'name' => $validated['name'],
            'is_active' => true,
        ]);

        return redirect()->route('measurement-units.index')->with('success', 'Unità di misura creata correttamente.');
    }

    /**
     * Mostra il form di modifica.
     */
    public function edit(MeasurementUnit $measurementUnit)
    {
        $this->authorize('update', $measurementUnit);
        return view('measurement_units.edit', compact('measurementUnit'));
    }

    /**
     * Aggiorna.
     */
    public function update(Request $request, MeasurementUnit $measurementUnit)
    {
        $this->authorize('update', $measurementUnit);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:measurement_units,name,' . $measurementUnit->id,
            'is_active' => 'boolean',
        ], [
            'name.unique' => 'Esiste già un\'unità di misura con questo nome.'
        ]);
        
        $measurementUnit->update([
            'name' => $validated['name'],
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('measurement-units.index')->with('success', 'Unità di misura aggiornata correttamente.');
    }

    /**
     * Abilita (scorciatoia)
     */
    public function activate(MeasurementUnit $measurementUnit)
    {
        $this->authorize('update', $measurementUnit);
        $measurementUnit->update(['is_active' => true]);
        return back()->with('success', 'Unità di misura riattivata.');
    }

    /**
     * Disabilita (scorciatoia)
     */
    public function deactivate(MeasurementUnit $measurementUnit)
    {
        $this->authorize('update', $measurementUnit);
        $measurementUnit->update(['is_active' => false]);
        return back()->with('success', 'Unità di misura disattivata.');
    }
}
