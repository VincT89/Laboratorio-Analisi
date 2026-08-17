<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\DocumentType;
use App\Models\Sample;
use App\Models\SampleType;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Tests\TestCase;

class SampleWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Sample $sample;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        $client = Client::create(['type' => 'company', 'company_name' => 'Factory Srl']);
        $type = SampleType::factory()->create();
        $this->sample = Sample::create([
            'client_id' => $client->id,
            'sample_type_id' => $type->id,
            'sample_type' => 'A',
            'collection_site' => 'Site',
            'collected_by' => 'User',
            'collected_at' => now(),
            'code' => '0001/26',
            'code_progressive' => 1,
            'code_year' => 26,
            'status' => 'collected',
            'created_by' => $this->admin->id,
        ]);
    }

    private function validUpdatePayload(array $overrides = []): array
    {
        return array_merge([
            'client_id' => $this->sample->client_id,
            'collected_at' => $this->sample->collected_at->format('Y-m-d'),
            'sample_type_id' => $this->sample->sample_type_id,
            'collection_site' => $this->sample->collection_site,
            'collected_by' => $this->sample->collected_by,
        ], $overrides);
    }

    public function test_staff_can_edit_standard_sample_status_from_form()
    {
        $staff = User::factory()->create();
        $staff->assignRole('staff');

        $response = $this->actingAs($staff)->patch(
            route('samples.update', $this->sample),
            $this->validUpdatePayload(['status' => 'completed'])
        );

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('samples.show', $this->sample));

        $this->sample->refresh();
        $this->assertEquals('completed', $this->sample->status);
        $this->assertNotNull($this->sample->accepted_at);
        $this->assertEquals($staff->id, $this->sample->updated_by);
    }

    public function test_standard_sample_can_be_returned_to_collected()
    {
        $staff = User::factory()->create();
        $staff->assignRole('staff');
        $this->sample->update([
            'status' => 'completed',
            'accepted_at' => now(),
        ]);

        $response = $this->actingAs($staff)->patch(
            route('samples.update', $this->sample),
            $this->validUpdatePayload(['status' => 'collected'])
        );

        $response->assertSessionHasNoErrors();

        $this->sample->refresh();
        $this->assertEquals('collected', $this->sample->status);
        $this->assertNull($this->sample->accepted_at);
    }

    public function test_sensitive_sample_status_cannot_be_changed_from_edit_form()
    {
        $sensitiveType = SampleType::factory()->create([
            'is_sensitive' => true,
        ]);

        $this->sample->update([
            'sample_type_id' => $sensitiveType->id,
            'sample_type' => $sensitiveType->name,
        ]);
        $this->sample->refresh();

        $response = $this->actingAs($this->admin)->from(route('samples.edit', $this->sample))->patch(
            route('samples.update', $this->sample),
            $this->validUpdatePayload(['status' => 'completed'])
        );

        $response->assertRedirect(route('samples.edit', $this->sample));
        $response->assertSessionHasErrors('status');

        $this->assertEquals('collected', $this->sample->fresh()->status);
    }

    public function test_accept_transition_works_and_sets_accepted_at()
    {
        $response = $this->actingAs($this->admin)->patch(route('samples.accept', $this->sample));

        $response->assertRedirect();

        $this->sample->refresh();
        $this->assertEquals('accepted', $this->sample->status);
        $this->assertNotNull($this->sample->accepted_at);
    }

    public function test_cannot_complete_if_not_accepted()
    {
        // Il campione è ancora 'collected'
        $response = $this->actingAs($this->admin)->patch(route('samples.complete', $this->sample));

        $response->assertStatus(403);
    }

    public function test_upload_file_on_completed_status_is_allowed()
    {
        // Accetta e completa il campione
        $this->sample->update(['status' => 'completed', 'accepted_at' => now()]);

        // Manda una POST per caricare un file (simulato)
        // Siccome il file validation richiede un uploaded file, passiamo uno stub o controlliamo l'autorizzazione generica
        $documentType = DocumentType::where('code', 'revised_report')->firstOrFail();
        $response = $this->actingAs($this->admin)->post(route('samples.files.store', $this->sample), [
            'document_type_id' => $documentType->id,
            'file' => File::create('test.pdf', 100),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sample_files', [
            'sample_id' => $this->sample->id,
            'document_type_id' => $documentType->id,
            'type' => 'revised_report',
        ]);
        $this->sample->refresh();
        $this->assertFalse((bool) $this->sample->archived);
    }

    public function test_accepted_at_cannot_be_altered_via_update_api()
    {
        $this->sample->update(['accepted_at' => null, 'status' => 'collected']);

        // Tentativo di settare accepted_at a una data diversa forzata
        $response = $this->actingAs($this->admin)->put(route('samples.update', $this->sample), [
            'client_id' => $this->sample->client_id,
            'collected_at' => now()->subDays(2)->format('Y-m-d'),
            'sample_type_id' => SampleType::factory()->create()->id,
            'collection_site' => 'Lab 1',
            'collected_by' => 'Mario Rossi',
            'accepted_at' => now()->format('Y-m-d H:i:s'), // Attacco: cerchiamo di immettere accepted_at
        ]);

        $response->assertRedirect();

        $this->sample->refresh();
        $this->assertNull($this->sample->accepted_at); // Non deve essersi salvato
    }

    public function test_reject_transition_works()
    {
        $response = $this->actingAs($this->admin)->patch(route('samples.reject', $this->sample));

        $response->assertRedirect();

        $this->sample->refresh();
        $this->assertEquals('rejected', $this->sample->status);
    }

    public function test_cannot_reject_completed_sample()
    {
        $staff = User::factory()->create();
        $staff->assignRole('staff');

        $this->sample->update(['status' => 'completed']);

        $response = $this->actingAs($staff)->patch(route('samples.reject', $this->sample));
        $response->assertStatus(403);
    }
}
