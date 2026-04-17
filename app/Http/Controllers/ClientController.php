<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Http\Requests\StoreClientRequest;
use App\Http\Requests\UpdateClientRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientController extends Controller
{
    /**
     * Lista dei clienti attivi con ricerca e filtri.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Client::class);

        $clients = Client::active()
            ->when($request->search, function ($query, $search) {
                // Ricerca parziale su ragione sociale, nome e cognome
                $query->where(function ($q) use ($search) {
                    $q->where('company_name', 'like', "%{$search}%")
                      ->orWhere('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%");
                });
            })
            ->orderBy('company_name')
            ->paginate(20)
            ->withQueryString();

        return view('clients.index', compact('clients'));
    }

    /**
     * Form per la creazione di un nuovo cliente.
     */
    public function create()
    {
        $this->authorize('create', Client::class);

        return view('clients.create');
    }

    /**
     * Salva un nuovo cliente nel database.
     */
    public function store(StoreClientRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = Auth::id();

        $client = Client::create($data);

        // Se la richiesta è AJAX (Fetch), restituiamo JSON invece del redirect
        if ($request->wantsJson()) {
            $displayName = $client->company_name ?: $client->first_name . ' ' . $client->last_name;
            if ($client->tax_code) {
                $displayName .= ' (' . $client->tax_code . ')';
            }

            return response()->json([
                'success' => true,
                'message' => 'Cliente creato correttamente.',
                'client' => [
                    'id' => $client->id,
                    'text' => $displayName
                ]
            ]);
        }

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Cliente creato correttamente.');
    }

    /**
     * Dettaglio di un cliente con i suoi campioni.
     */
    public function show(Client $client)
    {
        $this->authorize('view', $client);

        $client->load(['samples' => function ($query) {
            $query->active()->orderByDesc('collected_at');
        }]);

        return view('clients.show', compact('client'));
    }

    /**
     * Form per la modifica di un cliente.
     */
    public function edit(Client $client)
    {
        $this->authorize('update', $client);

        return view('clients.edit', compact('client'));
    }

    /**
     * Aggiorna un cliente nel database.
     */
    public function update(UpdateClientRequest $request, Client $client)
    {
        $client->update($request->validated());

        return redirect()
            ->route('clients.show', $client)
            ->with('success', 'Cliente aggiornato correttamente.');
    }

    /**
     * Archivia un cliente e in cascata i suoi campioni.
     */
    public function archive(Client $client)
    {
        $this->authorize('archive', $client);

        DB::transaction(function () use ($client) {
            $client->samples()->each(function ($sample) {
                $sample->files()->update([
                    'archived'    => true,
                    'archived_at' => now(),
                    'archived_by' => Auth::id(),
                ]);
            });

            // Archivia in cascata tutti i campioni del cliente
            $client->samples()->update([
                'archived'    => true,
                'archived_at' => now(),
                'archived_by' => Auth::id(),
            ]);

            $client->update([
                'archived'    => true,
                'archived_at' => now(),
                'archived_by' => Auth::id(),
            ]);
        });

        return redirect()
            ->route('clients.index')
            ->with('success', 'Cliente archiviato correttamente.');
    }

    /**
     * Ripristino completo:
     * - client
     * - campioni associati
     * - file dei campioni
     * Lo stato operativo (status) NON viene modificato
     */
    public function restore(Client $client)
    {
        $this->authorize('restore', $client);

        DB::transaction(function () use ($client) {
            $client->update([
                'archived'    => false,
                'archived_at' => null,
                'archived_by' => null,
            ]);

            $client->samples()->each(function ($sample) {
                $sample->files()->update([
                    'archived'    => false,
                    'archived_at' => null,
                    'archived_by' => null,
                ]);
            });

            $client->samples()->update([
                'archived'    => false,
                'archived_at' => null,
                'archived_by' => null,
            ]);
        });

        return redirect()
            ->route('clients.archived')
            ->with('success', 'Cliente ripristinato correttamente.');
    }

    /**
     * Elimina definitivamente un cliente (solo admin).
     */
    public function destroy(Client $client)
    {
        $this->authorize('delete', $client);

        // Cleanup dei file fisici in storage (private/samples/{sample_id})
        foreach ($client->samples as $sample) {
            $path = "samples/{$sample->id}";
            if (\Illuminate\Support\Facades\Storage::disk('private')->exists($path)) {
                $deleted = \Illuminate\Support\Facades\Storage::disk('private')->deleteDirectory($path);
                if (!$deleted) {
                    \Illuminate\Support\Facades\Log::warning("Impossibile cancellare la directory fisica del campione {$sample->id} durante l'eliminazione del cliente {$client->id}");
                }
            }
        }

        $client->delete();

        return redirect()
            ->route('clients.index')
            ->with('success', 'Cliente eliminato definitivamente.');
    }

    /**
     * Lista dei clienti archiviati.
     */
    public function archived()
    {
        $this->authorize('viewAny', Client::class);

        $clients = Client::archived()
            ->orderBy('archived_at', 'desc')
            ->paginate(20);

        return view('clients.archived', compact('clients'));
    }
}