<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Sample;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SampleWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Sample $sample;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        $client = Client::create(['type' => 'company', 'company_name' => 'Factory Srl']);
        $type = \App\Models\SampleType::factory()->create();
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
            'created_by' => $this->admin->id
        ]);
    }

    public function test_cannot_change_status_bypassing_workflow_via_update()
    {
        // Manda una put cercando di sbiancare lo stato a 'completed' illecitamente
        $response = $this->actingAs($this->admin)->patch(route('samples.update', $this->sample), [
            'client_id' => $this->sample->client_id,
            'collected_at' => $this->sample->collected_at,
            'sample_type_id' => $this->sample->sample_type_id,
            'collection_site' => $this->sample->collection_site,
            'collected_by' => $this->sample->collected_by,
            'status' => 'completed' // Attacco
        ]);

        $response->assertSessionHasNoErrors();
        
        $this->sample->refresh();
        $this->assertEquals('collected', $this->sample->status);
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
        $response = $this->actingAs($this->admin)->post(route('samples.files.store', $this->sample), [
            'type' => 'revised_report',
            'file' => \Illuminate\Http\Testing\File::create('test.pdf', 100),
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sample_files', [
            'sample_id' => $this->sample->id,
            'type' => 'revised_report'
        ]);
        $this->sample->refresh();
        $this->assertFalse((bool) $this->sample->archived);
    }

    public function test_accepted_at_cannot_be_altered_via_update_API()
    {
        $this->sample->update(['accepted_at' => null, 'status' => 'collected']);

        // Tentativo di settare accepted_at a una data diversa forzata
        $response = $this->actingAs($this->admin)->put(route('samples.update', $this->sample), [
            'client_id' => $this->sample->client_id,
            'collected_at' => now()->subDays(2)->format('Y-m-d'),
            'sample_type_id' => \App\Models\SampleType::factory()->create()->id,
            'collection_site' => 'Lab 1',
            'collected_by' => 'Mario Rossi',
            'accepted_at' => now()->format('Y-m-d H:i:s') // Attacco: cerchiamo di immettere accepted_at
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
