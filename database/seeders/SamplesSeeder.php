<?php

namespace Database\Seeders;

use App\Actions\Samples\AcceptSampleAction;
use App\Actions\Samples\ArchiveSampleAction;
use App\Actions\Samples\Workflow\CompleteSampleAction;
use App\Actions\Samples\CreateSampleAction;
use App\Actions\Samples\UpdateSampleAction;
use App\Models\Client;
use App\Models\Sample;
use App\Models\SampleType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class SamplesSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@aerconsulting.it')->firstOrFail();
        $staffUsers = User::role('staff')->get();

        $clients = Client::active()->get();
        $standardTypes = SampleType::where('is_active', true)->where('is_sensitive', false)->get();
        $sensitiveTypes = SampleType::where('is_active', true)->where('is_sensitive', true)->get();

        if ($clients->isEmpty() || $standardTypes->isEmpty() || $sensitiveTypes->isEmpty() || $staffUsers->isEmpty()) {
            $this->command?->warn('SamplesSeeder saltato: mancano utenti, clienti o sample types.');
            return;
        }

        $createAction = app(CreateSampleAction::class);
        $updateAction = app(UpdateSampleAction::class);
        $acceptAction = app(\App\Actions\Samples\Workflow\AcceptSampleAction::class);
        $completeAction = app(\App\Actions\Samples\Workflow\CompleteSampleAction::class);
        $archiveAction = app(\App\Actions\Samples\Workflow\ArchiveSampleAction::class);

        $sites = [
            'Bari - Via Sparano',
            'Bari - Zona Industriale',
            'Modugno - Area ASI',
            'Bitonto - Centro prelievi',
            'Molfetta - Porto',
            'Monopoli - Stabilimento costiero',
            'Conversano - Laboratorio mobile',
        ];

        $collectors = [
            'Dott. Ruggiero',
            'Dott.ssa Palmisano',
            'Tecnico Lorusso',
            'Tecnico De Gennaro',
            'Operatore Interno',
        ];

        $notes = [
            'Campione conforme alla procedura standard.',
            'Prelievo effettuato in ambiente controllato.',
            'Richiesta prioritaria del cliente.',
            'Verifica periodica programmata.',
            'Campione da monitoraggio di routine.',
        ];

        $createdSamples = collect();

        // 12 standard collected
        $createdSamples = $createdSamples->merge(
            $this->createStandardSamples(
                12,
                $staffUsers,
                $clients,
                $standardTypes,
                $sites,
                $collectors,
                $notes,
                $createAction
            )
        );

        // 8 standard accepted
        $acceptedSamples = $this->createStandardSamples(
            8,
            $staffUsers,
            $clients,
            $standardTypes,
            $sites,
            $collectors,
            $notes,
            $createAction
        );

        foreach ($acceptedSamples as $sample) {
            $acceptAction->execute($sample->fresh(), $admin->id);
        }

        $createdSamples = $createdSamples->merge($acceptedSamples);

        // 6 standard completed
        $completedSamples = $this->createStandardSamples(
            6,
            $staffUsers,
            $clients,
            $standardTypes,
            $sites,
            $collectors,
            $notes,
            $createAction
        );

        foreach ($completedSamples as $sample) {
            $acceptAction->execute($sample->fresh(), $admin->id);
            $completeAction->execute($sample->fresh(), $admin->id);
        }

        $createdSamples = $createdSamples->merge($completedSamples);

        // 6 sensibili incompleti (preregistrazioni tecniche)
        $sensitiveIncomplete = $this->createSensitiveSamples(
            6,
            $staffUsers,
            $sensitiveTypes,
            $sites,
            $collectors,
            $createAction
        );

        $createdSamples = $createdSamples->merge($sensitiveIncomplete);

        // 4 sensibili completabili e poi completati da admin
        $sensitiveCompleted = $this->createSensitiveSamples(
            4,
            $staffUsers,
            $sensitiveTypes,
            $sites,
            $collectors,
            $createAction
        );

        foreach ($sensitiveCompleted as $sample) {
            $client = $clients->random();

            $updateAction->execute($sample, [
                'client_id' => $client->id,
                'sample_type_id' => $sample->sample_type_id,
                'collected_at' => $sample->collected_at,
                'collection_site' => $sample->collection_site,
                'collected_by' => $sample->collected_by,
                'notes' => null,
                'container_type_id' => \App\Models\ContainerType::inRandomOrder()->first()?->id,
                'conservation_status' => fake()->randomElement(['A temperatura ambiente', 'Refrigerato', 'Congelato', 'Al riparo dalla luce']),
                'sample_quantity' => fake()->randomElement(['100 ml', '500 ml', '1 L', '250 g', '2 tamponi']),
                'lab_archived_by_name' => fake()->randomElement(['Dr. Rossi', 'Tec. Bianchi', 'Amministratore Lab']),
            ], $admin->id);

            $acceptAction->execute($sample->fresh(), $admin->id);
            $completeAction->execute($sample->fresh(), $admin->id);
        }

        $createdSamples = $createdSamples->merge($sensitiveCompleted);

        // Archivia alcuni campioni già esistenti
        $archivable = Sample::active()->where('status', 'completed')->inRandomOrder()->take(5)->get();
        foreach ($archivable as $sample) {
            $archiveAction->execute($sample->fresh(), $admin->id);
        }

        $this->command?->info('Campioni demo creati correttamente.');
    }

    private function createStandardSamples(
        int $count,
        Collection $staffUsers,
        Collection $clients,
        Collection $standardTypes,
        array $sites,
        array $collectors,
        array $notes,
        CreateSampleAction $createAction
    ): Collection {
        $samples = collect();

        for ($i = 0; $i < $count; $i++) {
            $staff = $staffUsers->random();
            $type = $standardTypes->random();
            $client = $clients->random();

            $samples->push(
                $createAction->execute([
                    'client_id' => $client->id,
                    'sample_type_id' => $type->id,
                    'collected_at' => now()->subDays(rand(1, 45))->toDateString(),
                    'collection_site' => fake()->randomElement($sites),
                    'collected_by' => fake()->randomElement($collectors),
                    'notes' => fake()->randomElement($notes),
                    'container_type_id' => \App\Models\ContainerType::inRandomOrder()->first()?->id,
                    'conservation_status' => fake()->randomElement(['A temperatura ambiente', 'Refrigerato', 'Congelato', 'Al riparo dalla luce']),
                    'sample_quantity' => fake()->randomElement(['100 ml', '500 ml', '1 L', '250 g', '2 tamponi']),
                    'lab_archived_by_name' => fake()->randomElement(['Dr. Rossi', 'Tec. Bianchi', 'Amministratore Lab']),
                ], $staff->id)
            );
        }

        return $samples;
    }

    private function createSensitiveSamples(
        int $count,
        Collection $staffUsers,
        Collection $sensitiveTypes,
        array $sites,
        array $collectors,
        CreateSampleAction $createAction
    ): Collection {
        $samples = collect();

        for ($i = 0; $i < $count; $i++) {
            $staff = $staffUsers->random();
            $type = $sensitiveTypes->random();

            $samples->push(
                $createAction->execute([
                    'sample_type_id' => $type->id,
                    'collected_at' => now()->subDays(rand(1, 30))->toDateString(),
                    'collection_site' => fake()->randomElement($sites),
                    'collected_by' => fake()->randomElement($collectors),
                    'client_id' => null,
                    'notes' => 'Questo valore verrà annullato dalla action',
                ], $staff->id)
            );
        }

        return $samples;
    }
}
