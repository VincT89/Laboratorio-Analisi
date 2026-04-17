<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Sample;
use App\Models\SampleFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SampleFileAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $staff;
    private Sample $sampleA;
    private Sample $sampleB;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
        
        $this->staff = User::factory()->create();
        $this->staff->assignRole('staff');

        $client = Client::create(['type' => 'company', 'company_name' => 'Factory Srl']);
        
        $this->sampleA = Sample::create(['client_id' => $client->id, 'sample_type' => 'A', 'collection_site' => 'Site', 'collected_by' => 'User', 'collected_at' => now(), 'code' => 'LAB-2026-00001', 'created_by' => $this->admin->id]);
        $this->sampleB = Sample::create(['client_id' => $client->id, 'sample_type' => 'B', 'collection_site' => 'Site', 'collected_by' => 'User', 'collected_at' => now(), 'code' => 'LAB-2026-00002', 'created_by' => $this->admin->id]);
        
        Storage::fake('private');
    }

    public function test_cannot_access_file_belonging_to_another_sample()
    {
        // Creiamo un file per il sample A
        $fileA = SampleFile::create([
            'sample_id' => $this->sampleA->id,
            'original_name' => 'test.pdf',
            'type' => 'report',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size' => 100,
            'path' => 'fake/path.pdf',
            'uploaded_by' => $this->admin->id
        ]);

        // Proviamo a scaricarlo passando l'id del sample B (Cross-Reference bug)
        $response = $this->actingAs($this->admin)->get(route('samples.files.download', [
            'sample' => $this->sampleB->id,
            'sampleFile' => $fileA->id
        ]));

        $response->assertStatus(404);
    }

    public function test_cannot_upload_file_to_archived_sample()
    {
        $this->sampleA->update(['archived' => true]);

        $file = UploadedFile::fake()->create('report.pdf', 100);

        $response = $this->actingAs($this->admin)->post(route('samples.files.store', $this->sampleA), [
            'file' => $file,
            'type' => 'report'
        ]);

        $response->assertStatus(403);
    }

    public function test_cannot_download_archived_file()
    {
        $fileA = SampleFile::create([
            'sample_id' => $this->sampleA->id,
            'path' => 'fake/path.pdf',
            'original_name' => 'test.pdf',
            'type' => 'report',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size' => 100,
            'archived' => true,
            'uploaded_by' => $this->admin->id
        ]);

        $response = $this->actingAs($this->staff)->get(route('samples.files.download', [
            'sample' => $this->sampleA->id,
            'sampleFile' => $fileA->id
        ]));

        $response->assertStatus(403);
    }
}
