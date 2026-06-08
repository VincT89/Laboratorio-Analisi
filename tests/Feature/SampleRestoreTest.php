<?php

namespace Tests\Feature;

use App\Models\Sample;
use App\Models\SampleFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SampleRestoreTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    public function test_sample_restore_restores_sample_and_its_files()
    {
        $client = \App\Models\Client::create(['type' => 'company', 'company_name' => 'Factory Srl']);
        
        $sample = Sample::create([
            'client_id' => $client->id,
            'archived' => true,
            'archived_at' => now(),
            'code' => '0001/26',
            'code_progressive' => 1,
            'code_year' => 26,
            'sample_type_id' => \App\Models\SampleType::factory()->create()->id,
            'sample_type' => 'X',
            'collection_site' => 'Y',
            'collected_by' => 'Z',
            'collected_at' => now(),
            'status' => 'collected',
            'created_by' => $this->admin->id
        ]);

        $file = SampleFile::create([
            'sample_id' => $sample->id,
            'original_name' => 'doc.pdf',
            'path' => 'files/doc.pdf',
            'uploaded_by' => $this->admin->id,
            'archived' => true,
            'archived_at' => now()
        ]);

        $response = $this->actingAs($this->admin)->patch(route('samples.restore', $sample));
        
        $response->assertRedirect(route('samples.archived'));
        
        $this->assertFalse($sample->fresh()->archived);
        $this->assertFalse($file->fresh()->archived);
    }

    public function test_staff_cannot_archive_sample()
    {
        $staff = User::factory()->create();
        $staff->assignRole('staff');

        $client = \App\Models\Client::create(['type' => 'company', 'company_name' => 'F2']);
        $sample = Sample::create([
            'client_id' => $client->id,
            'archived' => false,
            'code' => '0002/26',
            'code_progressive' => 2,
            'code_year' => 26,
            'sample_type_id' => \App\Models\SampleType::factory()->create()->id,
            'sample_type' => 'X',
            'collection_site' => 'Y',
            'collected_by' => 'Z',
            'collected_at' => now(),
            'status' => 'collected',
            'created_by' => $this->admin->id
        ]);

        $response = $this->actingAs($staff)->patch(route('samples.archive', $sample));
        $response->assertStatus(403);
    }
}
