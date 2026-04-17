<?php

namespace App\Http\Controllers;

use App\Models\Sample;
use App\Models\SampleFile;
use App\Http\Requests\StoreSampleFileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class SampleFileController extends Controller
{
    private function ensureFileBelongsToSample(Sample $sample, SampleFile $sampleFile): void
    {
        abort_unless($sampleFile->sample_id === $sample->id, 404, 'File non appartenente a questo campione.');
    }

    /**
     * Carica un file sul campione.
     * Consentito anche per campioni completati.
     * Non consentito per campioni archiviati.
     */
    public function store(StoreSampleFileRequest $request, Sample $sample)
    {
        $this->authorize('create', SampleFile::class);
        
        abort_if($sample->isSensitive() && auth()->user()->hasRole('staff'), 403, 'Non disponi dei permessi per gestire file su un campione con privacy elevata.');
        abort_if($sample->archived, 403, 'Non puoi caricare file su un campione archiviato.');

        $file = $request->file('file');

        // Salva il file in storage privato, organizzato per campione
        $path = $file->store("samples/{$sample->id}", 'private');

        SampleFile::create([
            'sample_id'     => $sample->id,
            'original_name' => $file->getClientOriginalName(),
            'path'          => $path,
            'type'          => $request->type,
            'mime_type'     => $file->getMimeType(),
            'extension'     => $file->getClientOriginalExtension(),
            'size'          => $file->getSize(),
            'description'   => $request->description,
            'uploaded_by'   => Auth::id(),
        ]);

        $tipoDoc = $request->type === 'report' ? 'Referto' : ($request->type === 'prescription' ? 'Certificato' : 'Allegato');
        
        activity()
            ->performedOn($sample)
            ->causedBy(Auth::user())
            ->log("Caricato {$tipoDoc}: " . $file->getClientOriginalName());

        return redirect()
            ->route('samples.show', $sample)
            ->with('success', 'File caricato correttamente.');
    }

    /**
     * Scarica un file in modo sicuro tramite backend autenticato.
     * Il file non ha URL pubblica.
     */
    public function download(Sample $sample, SampleFile $sampleFile)
    {
        $this->ensureFileBelongsToSample($sample, $sampleFile);
        Gate::authorize('download', $sampleFile);

        $path = Storage::disk('private')->path($sampleFile->path);

        if (!file_exists($path)) {
            abort(404, 'File non trovato.');
        }

        return response()->download($path, $sampleFile->original_name, [
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }



    /**
     * Archivia un file.
     */
    public function archive(Sample $sample, SampleFile $sampleFile)
    {
        $this->ensureFileBelongsToSample($sample, $sampleFile);
        $this->authorize('archive', $sampleFile);

        $sampleFile->update([
            'archived'    => true,
            'archived_at' => now(),
            'archived_by' => Auth::id(),
        ]);

        return redirect()
            ->route('samples.show', $sample)
            ->with('success', 'File archiviato correttamente.');
    }

    /**
     * Elimina definitivamente un file (solo admin).
     */
    public function destroy(Sample $sample, SampleFile $sampleFile)
    {
        $this->ensureFileBelongsToSample($sample, $sampleFile);
        $this->authorize('delete', $sampleFile);

        // Elimina il file fisico dallo storage
        Storage::disk('private')->delete($sampleFile->path);

        $fileName = $sampleFile->original_name;
        $sampleFile->delete();

        activity()
            ->performedOn($sample)
            ->causedBy(Auth::user())
            ->log("Eliminato documento: " . $fileName);

        return redirect()
            ->route('samples.show', $sample)
            ->with('success', 'File eliminato definitivamente.');
    }
}