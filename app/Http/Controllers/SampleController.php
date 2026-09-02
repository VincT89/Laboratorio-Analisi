<?php

namespace App\Http\Controllers;

use App\Actions\Samples\CreateSampleAction;
use App\Actions\Samples\UpdateSampleAction;
use App\Actions\Samples\Workflow\AcceptSampleAction;
use App\Actions\Samples\Workflow\ArchiveSampleAction;
use App\Actions\Samples\Workflow\CompleteSampleAction;
use App\Actions\Samples\Workflow\RejectSampleAction;
use App\Actions\Samples\Workflow\RestoreSampleAction;
use App\Http\Requests\StoreSampleRequest;
use App\Http\Requests\UpdateSampleRequest;
use App\Models\Client;
use App\Models\ConservationStatus;
use App\Models\ContainerType;
use App\Models\DocumentType;
use App\Models\MeasurementUnit;
use App\Models\Sample;
use App\Models\SampleType;
use App\Queries\Samples\ActiveSamplesIndexQuery;
use App\Queries\Samples\ArchivedSamplesIndexQuery;
use App\Queries\Samples\SampleMetricsQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SampleController extends Controller
{
    public function __construct(
        private ActiveSamplesIndexQuery $activeIndexQuery,
        private ArchivedSamplesIndexQuery $archivedIndexQuery,
        private SampleMetricsQuery $metricsQuery
    ) {}

    /**
     * Lista dei campioni attivi con ricerca e filtri.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Sample::class);

        $samples = $this->activeIndexQuery->paginate($request->all(), Auth::user());
        $metrics = $this->metricsQuery->get();

        return view('samples.index', compact('samples', 'metrics'));
    }

    /**
     * Form per la creazione di un nuovo campione.
     */
    public function create(Request $request)
    {
        $this->authorize('create', Sample::class);

        $mode = $request->query('mode');

        // Se arrivo dalla scheda cliente, preseleziono il cliente
        $selectedClient = $request->client_id
            ? Client::active()->findOrFail($request->client_id)
            : null;

        $sampleTypes = SampleType::where('is_active', true)->orderBy('name')->get();
        $containerTypes = ContainerType::where('is_active', true)->orderBy('name')->get();

        $conservationStatuses = ConservationStatus::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name', 'name');

        $quantityUnits = MeasurementUnit::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name', 'name');

        return view('samples.create', compact('selectedClient', 'sampleTypes', 'containerTypes', 'mode', 'conservationStatuses', 'quantityUnits'));
    }

    public function store(StoreSampleRequest $request, CreateSampleAction $createSample)
    {
        $sample = $createSample->execute($request->validated(), Auth::id());

        if ($sample->isSensitive()) {
            return redirect()
                ->route('samples.index')
                ->with('success', "Campione sensibile preregistrato correttamente. Codice: {$sample->code}. I dettagli esposti sono riservati agli amministratori.");
        }

        return redirect()
            ->route('samples.show', $sample)
            ->with('success', 'Campione creato correttamente.');
    }

    /**
     * Dettaglio di un campione con tab: dati, accettazione, file, storico.
     */
    public function show(Sample $sample)
    {
        $this->authorize('view', $sample);

        $sample->load([
            'client',
            'containerType',
            'files' => fn ($q) => $q->with('documentType')->active()->orderByDesc('created_at'),
            'createdBy',
            'updatedBy',
        ]);

        $activities = $sample->activities()->orderByDesc('created_at')->get();
        $documentTypes = DocumentType::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('samples.show', compact('sample', 'activities', 'documentTypes'));
    }

    /**
     * Form per la modifica di un campione.
     */
    public function edit(Sample $sample)
    {
        $this->authorize('update', $sample);

        $sampleTypes = SampleType::where('is_active', true)
            ->orWhere('id', $sample->sample_type_id)
            ->orderBy('name')
            ->get();

        $containerTypes = ContainerType::where('is_active', true)
            ->orWhere('id', $sample->container_type_id)
            ->orderBy('name')
            ->get();

        $conservationStatuses = ConservationStatus::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name', 'name');

        $quantityUnits = MeasurementUnit::active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name', 'name');

        return view('samples.edit', compact('sample', 'sampleTypes', 'containerTypes', 'conservationStatuses', 'quantityUnits'));
    }

    /**
     * Aggiorna un campione nel database.
     */
    public function update(UpdateSampleRequest $request, Sample $sample, UpdateSampleAction $updateSample)
    {
        $sample = $updateSample->execute($sample, $request->validated(), Auth::id());

        return redirect()
            ->route('samples.show', $sample)
            ->with('success', 'Campione aggiornato correttamente.');
    }

    /**
     * Accetta un campione.
     */
    public function accept(Sample $sample, AcceptSampleAction $action)
    {
        $this->authorize('accept', $sample);

        $action->execute($sample, Auth::id());

        return redirect()
            ->route('samples.show', $sample)
            ->with('success', 'Campione accettato correttamente.');
    }

    /**
     * Segna il campione come completato.
     */
    public function complete(Sample $sample, CompleteSampleAction $action)
    {
        $this->authorize('complete', $sample);

        $action->execute($sample, Auth::id());

        return redirect()
            ->route('samples.show', $sample)
            ->with('success', 'Campione completato correttamente.');
    }

    /**
     * Rifiuta un campione.
     */
    public function reject(Sample $sample, RejectSampleAction $action)
    {
        $this->authorize('reject', $sample);

        $action->execute($sample, Auth::id());

        return redirect()
            ->route('samples.show', $sample)
            ->with('success', 'Campione rifiutato correttamente.');
    }

    /**
     * Archivia il campione e tutti i file associati in cascata.
     */
    public function archive(Sample $sample, ArchiveSampleAction $action)
    {
        $this->authorize('archive', $sample);

        $action->execute($sample, Auth::id());

        return redirect()
            ->route('samples.index')
            ->with('success', 'Campione archiviato correttamente.');
    }

    /**
     * Ripristina il campione e tutti i file associati.
     * Non modifica lo stato del workflow (status).
     */
    public function restore(Sample $sample, RestoreSampleAction $action)
    {
        $this->authorize('restore', $sample);

        $action->execute($sample, Auth::id());

        return redirect()
            ->route('samples.archived')
            ->with('success', 'Campione ripristinato correttamente.');
    }

    /**
     * Elimina definitivamente un campione (solo admin).
     */
    public function destroy(Sample $sample)
    {
        $this->authorize('delete', $sample);

        // Cleanup fisico della cartella dei file associati al campione
        $path = "samples/{$sample->id}";
        if (Storage::disk('private')->exists($path)) {
            $deleted = Storage::disk('private')->deleteDirectory($path);
            if (! $deleted) {
                Log::warning("Impossibile cancellare la directory fisica del campione {$sample->id}");
            }
        }

        $sample->delete();

        return redirect()
            ->route('samples.index')
            ->with('success', 'Campione eliminato definitivamente.');
    }

    public function archived(Request $request)
    {
        $this->authorize('viewAny', Sample::class);

        $samples = $this->archivedIndexQuery->paginate($request->all(), Auth::user());

        return view('samples.archived', compact('samples'));
    }
}
