<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Sample;
use App\Models\SampleFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientRestoreTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        
        $this->staff = User::factory()->create();
        $this->staff->assignRole('staff');
    }

    public function test_client_cascade_restore_works_for_admin_and_restores_samples_and_files()
    {
        $client = Client::create([
            'type' => 'company', 
            'company_name' => 'Factory Srl',
            'archived' => true, 
            'archived_at' => now(), 
            'archived_by' => $this->admin->id
        ]);
        
        $sample = Sample::create([
            'client_id' => $client->id,
            'archived' => true,
            'archived_at' => now(),
            'code' => 'TEST-01',
            'sample_type' => 'A',
            'collection_site' => 'S',
            'collected_by' => 'U',
            'collected_at' => now(),
            'status' => 'collected',
            'built_in' => false,
            'created_by' => $this->admin->id
        ]);

        $file = SampleFile::create([
            'sample_id' => $sample->id,
            'original_name' => 'test.pdf',
            'path' => 'files/test.pdf',
            'uploaded_by' => $this->admin->id,
            'archived' => true,
            'archived_at' => now()
        ]);

        $response = $this->actingAs($this->admin)->patch(route('clients.restore', $client));
        
        $response->assertRedirect(route('clients.archived'));
        
        $this->assertFalse($client->fresh()->archived);
        $this->assertFalse($sample->fresh()->archived);
        $this->assertFalse($file->fresh()->archived);
    }

    public function test_staff_cannot_restore_client()
    {
        $client = Client::create([
            'type' => 'company', 
            'company_name' => 'Factory Srl',
            'archived' => true,
            'archived_at' => now(),
        ]);

        $response = $this->actingAs($this->staff)->patch(route('clients.restore', $client));
        
        $response->assertStatus(403);
    }

    public function test_staff_cannot_archive_client()
    {
        $client = Client::create([
            'type' => 'company', 
            'company_name' => 'Factory Srl',
            'archived' => false,
        ]);

        $response = $this->actingAs($this->staff)->patch(route('clients.archive', $client));
        
        $response->assertStatus(403);
    }
}
