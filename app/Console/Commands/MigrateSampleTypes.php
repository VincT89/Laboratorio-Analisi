<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\SampleType;

#[Signature('samples:migrate-types {--dry-run : Esegui senza salvare nulla nel database} {--fallback-name=Non Classificato : Nome del tipo per campioni senza sample_type}')]
#[Description('Migra il campo testuale sample_type verso la nuova entità governata SampleType usando deduplica conservativa.')]
class MigrateSampleTypes extends Command
{
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        if ($isDryRun) {
            $this->info("=== ESECUZIONE IN MODALITÀ DRY RUN ===");
        }

        $fallbackName = $this->option('fallback-name');

        $samples = DB::table('samples')
            ->whereNull('sample_type_id')
            ->get();

        if ($samples->isEmpty()) {
            $this->info("Nessun campione da migrare o tutti già mappati.");
            return;
        }

        $this->info("Trovati {$samples->count()} campioni da mappare.");

        // Mappa locale per evitare query ridondanti. [canonical => SampleType]
        $typesCache = [];
        $mappedCount = 0;
        $fallbackCount = 0;

        foreach ($samples as $sample) {
            $originalType = $sample->sample_type;

            if (empty(trim($originalType))) {
                $typeName = $fallbackName;
                $fallbackCount++;
            } else {
                $typeName = $this->canonicalizeAndFormat($originalType);
            }

            $canonicalKey = strtolower($typeName);
            $slugKey = \Illuminate\Support\Str::slug($typeName);

            if (!isset($typesCache[$canonicalKey])) {
                if ($isDryRun) {
                    $type = new SampleType(['id' => count($typesCache) + 1, 'name' => $typeName, 'slug' => $slugKey]);
                } else {
                    $type = SampleType::firstOrCreate(
                        ['slug' => $slugKey],
                        ['name' => $typeName]
                    );
                }
                $typesCache[$canonicalKey] = $type;
                $this->line("Creato/Trovato tipo: " . $type->name);
            }

            if (!$isDryRun) {
                DB::table('samples')
                    ->where('id', $sample->id)
                    ->update(['sample_type_id' => $typesCache[$canonicalKey]->id]);
            }
            $mappedCount++;
        }

        if ($isDryRun) {
            $this->info("\n=== DRY RUN COMPLETATO ===");
            $this->info("Campioni analizzati: {$mappedCount}");
            $this->info("Tipi unici individuati: " . count($typesCache));
            $this->info("Campioni assegnati a Fallback: {$fallbackCount}");
        } else {
            $this->info("\n=== MIGRAZIONE COMPLETATA ===");
            $this->info("Campioni mappati con successo: {$mappedCount}");
            $this->info("Tipi unici creati/trovati: " . count($typesCache));
            $this->info("Campioni assegnati a Fallback: {$fallbackCount}");
        }
    }

    /**
     * Pulisce gli spazi multipli e restituisce il nome formattato in Title Case preservando le specificità.
     */
    private function canonicalizeAndFormat(string $raw): string
    {
        // Rimuove spazi a inizio/fine e comprime spazi multipli in singolo spazio
        $clean = preg_replace('/\s+/', ' ', trim($raw));
        // Se si desidera ulteriore formattazione, la si applica qui, es. ucfirst.
        // Ma come da istruzioni, non facciamo TitleCase forzato se non per la prima lettera, 
        // lasciando inalterata la complessità stringa originale. 
        // Vogliamo Title Case come presentazione? Il piano dice:
        // "Title Case sì come presentazione, no come unica logica di fusione."
        // Qui 'strtolower($typeName)' è usato per la fusione.
        // Quindi posso restituire ucwords(strtolower($clean)) per una presentazione decente.
        return ucwords(strtolower($clean));
    }
}
