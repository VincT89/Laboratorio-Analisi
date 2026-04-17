<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Sample;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HardDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        
        $this->staff = User::factory()->create();
        $this->staff->assignRole('staff');
        
        Storage::fake('private');
    }

    public function test_active_records_do_not_show_hard_delete_button()
    {
        $client = Client::create(['type' => 'company', 'company_name' => 'A', 'archived' => false, 'created_by' => $this->admin->id]);
        $sample = Sample::create(['client_id' => $client->id, 'code' => 'TEST-01', 'sample_type' => 'A', 'collection_site' => 'B', 'collected_by' => 'C', 'collected_at' => now(), 'archived' => false, 'created_by' => $this->admin->id]);

        // Client UI
        $response = $this->actingAs($this->admin)->get(route('clients.show', $client));
        $response->assertOk();
        $response->assertDontSee('Elimina definitivamente');

        // Sample UI
        $response = $this->actingAs($this->admin)->get(route('samples.show', $sample));
        $response->assertOk();
        $response->assertDontSee('Elimina definitivamente');
    }

    public function test_archived_records_do_show_hard_delete_button()
    {
        $client = Client::create(['type' => 'company', 'company_name' => 'B', 'archived' => true, 'archived_at' => now(), 'created_by' => $this->admin->id]);
        $sample = Sample::create(['client_id' => $client->id, 'code' => 'TEST-02', 'sample_type' => 'A', 'collection_site' => 'B', 'collected_by' => 'C', 'collected_at' => now(), 'archived' => true, 'archived_at' => now(), 'created_by' => $this->admin->id]);

        // Client UI
        $response = $this->actingAs($this->admin)->get(route('clients.show', $client));
        $response->assertOk();
        $response->assertSee('Elimina definitivamente');

        // Sample UI
        $response = $this->actingAs($this->admin)->get(route('samples.show', $sample));
        $response->assertOk();
        $response->assertSee('Elimina definitivamente');
    }

    public function test_sample_destroy_deletes_directory_correctly()
    {
        $client = Client::create(['type' => 'company', 'company_name' => 'C', 'created_by' => $this->admin->id]);
        $sample = Sample::create(['client_id' => $client->id, 'code' => 'TEST-03', 'sample_type' => 'A', 'collection_site' => 'B', 'collected_by' => 'C', 'collected_at' => now(), 'created_by' => $this->admin->id]);

        Storage::disk('private')->makeDirectory("samples/{$sample->id}");
        $this->assertTrue(Storage::disk('private')->exists("samples/{$sample->id}"));

        $response = $this->actingAs($this->admin)->delete(route('samples.destroy', $sample));
        
        $response->assertRedirect(route('samples.index'));
        $this->assertDatabaseMissing('samples', ['id' => $sample->id]);
        $this->assertFalse(Storage::disk('private')->exists("samples/{$sample->id}"));
    }

    public function test_client_destroy_deletes_all_sample_directories()
    {
        $client = Client::create(['type' => 'company', 'company_name' => 'D', 'created_by' => $this->admin->id]);
        $sample1 = Sample::create(['client_id' => $client->id, 'code' => 'TEST-04', 'sample_type' => 'A', 'collection_site' => 'B', 'collected_by' => 'C', 'collected_at' => now(), 'created_by' => $this->admin->id]);
        $sample2 = Sample::create(['client_id' => $client->id, 'code' => 'TEST-05', 'sample_type' => 'A', 'collection_site' => 'B', 'collected_by' => 'C', 'collected_at' => now(), 'created_by' => $this->admin->id]);

        Storage::disk('private')->makeDirectory("samples/{$sample1->id}");
        Storage::disk('private')->makeDirectory("samples/{$sample2->id}");

        $response = $this->actingAs($this->admin)->delete(route('clients.destroy', $client));
        
        $response->assertRedirect(route('clients.index'));
        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
        $this->assertDatabaseMissing('samples', ['id' => $sample1->id]);
        $this->assertDatabaseMissing('samples', ['id' => $sample2->id]);
        
        $this->assertFalse(Storage::disk('private')->exists("samples/{$sample1->id}"));
        $this->assertFalse(Storage::disk('private')->exists("samples/{$sample2->id}"));
    }

    public function test_failed_delete_directory_logs_warning()
    {
        $client = Client::create(['type' => 'company', 'company_name' => 'E', 'created_by' => $this->admin->id]);
        $sample = Sample::create(['client_id' => $client->id, 'code' => 'TEST-06', 'sample_type' => 'A', 'collection_site' => 'B', 'collected_by' => 'C', 'collected_at' => now(), 'created_by' => $this->admin->id]);

        Storage::disk('private')->makeDirectory("samples/{$sample->id}");
        
        // Mock Storage per forzare un fallimento
        Storage::shouldReceive('disk')->with('private')->andReturnSelf();
        Storage::shouldReceive('exists')->with("samples/{$sample->id}")->andReturn(true);
        Storage::shouldReceive('deleteDirectory')->with("samples/{$sample->id}")->andReturn(false);

        // Mock Log per verificare che il warning venga girato
        Log::shouldReceive('warning')->once()->with("Impossibile cancellare la directory fisica del campione {$sample->id}");

        $this->actingAs($this->admin)->delete(route('samples.destroy', $sample));
    }

    public function test_missing_directory_does_not_fail_or_warn()
    {
        $client = Client::create(['type' => 'company', 'company_name' => 'F', 'created_by' => $this->admin->id]);
        $sample = Sample::create(['client_id' => $client->id, 'code' => 'TEST-07', 'sample_type' => 'A', 'collection_site' => 'B', 'collected_by' => 'C', 'collected_at' => now(), 'created_by' => $this->admin->id]);

        $this->assertFalse(Storage::disk('private')->exists("samples/{$sample->id}"));

        Log::shouldReceive('warning')->never();

        $response = $this->actingAs($this->admin)->delete(route('samples.destroy', $sample));
        $response->assertRedirect(route('samples.index'));
        $this->assertDatabaseMissing('samples', ['id' => $sample->id]);
    }

    public function test_client_destroy_deletes_both_active_and_archived_sample_directories()
    {
        $client = Client::create(['type' => 'company', 'company_name' => 'G', 'created_by' => $this->admin->id]);
        $activeSample = Sample::create(['client_id' => $client->id, 'code' => 'TEST-08', 'sample_type' => 'A', 'collection_site' => 'B', 'collected_by' => 'C', 'collected_at' => now(), 'archived' => false, 'created_by' => $this->admin->id]);
        $archivedSample = Sample::create(['client_id' => $client->id, 'code' => 'TEST-09', 'sample_type' => 'A', 'collection_site' => 'B', 'collected_by' => 'C', 'collected_at' => now(), 'archived' => true, 'archived_at' => now(), 'created_by' => $this->admin->id]);

        Storage::disk('private')->makeDirectory("samples/{$activeSample->id}");
        Storage::disk('private')->makeDirectory("samples/{$archivedSample->id}");

        $response = $this->actingAs($this->admin)->delete(route('clients.destroy', $client));
        
        $this->assertFalse(Storage::disk('private')->exists("samples/{$activeSample->id}"));
        $this->assertFalse(Storage::disk('private')->exists("samples/{$archivedSample->id}"));
        $this->assertDatabaseMissing('samples', ['id' => $archivedSample->id]);
    }

    public function test_full_workflow_from_active_to_archive_to_destroy()
    {
        $client = Client::create(['type' => 'company', 'company_name' => 'H', 'created_by' => $this->admin->id]);
        $sample = Sample::create(['client_id' => $client->id, 'code' => 'TEST-10', 'sample_type' => 'A', 'collection_site' => 'B', 'collected_by' => 'C', 'collected_at' => now(), 'archived' => false, 'created_by' => $this->admin->id]);
        
        // 1. View
        $response = $this->actingAs($this->admin)->get(route('samples.show', $sample));
        $response->assertDontSee('Elimina definitivamente');

        // 2. Archivia
        $response = $this->actingAs($this->admin)->patch(route('samples.archive', $sample));
        $response->assertRedirect(route('samples.index'));
        $this->assertTrue($sample->fresh()->archived);

        // 3. View dopo archiviazione
        $response = $this->actingAs($this->admin)->get(route('samples.show', $sample));
        $response->assertSee('Elimina definitivamente');

        // 4. Destroy fisico e query
        Storage::disk('private')->makeDirectory("samples/{$sample->id}");
        $this->actingAs($this->admin)->delete(route('samples.destroy', $sample));
        $this->assertDatabaseMissing('samples', ['id' => $sample->id]);
        $this->assertFalse(Storage::disk('private')->exists("samples/{$sample->id}"));
    }
}
