<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Sample;
use App\Models\SampleType;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SensitiveSampleWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;
    protected SampleType $sensitiveType;
    protected SampleType $standardType;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->staff = User::factory()->create();
        $this->staff->assignRole('staff');

        $this->sensitiveType = SampleType::factory()->create(['name' => 'Tossicologico', 'is_sensitive' => true, 'is_active' => true]);
        $this->standardType = SampleType::factory()->create(['name' => 'Acque', 'is_sensitive' => false, 'is_active' => true]);
    }

    public function test_staff_create_sensitive_minimale_forces_nulls_and_redirects()
    {
        $response = $this->actingAs($this->staff)->post(route('samples.store'), [
            'creation_mode' => 'sensitive',
            'collected_at' => now()->format('Y-m-d'),
            'sample_type_id' => $this->sensitiveType->id,
            'collection_site' => 'Site A',
            'collected_by' => 'Dr. Bob',
            // Even if staff tries to send client_id, the controller ignores it
            'client_id' => 999, 
            'notes' => 'Some sensitive notes', // This should be nulled out
        ]);

        $sample = Sample::first();
        
        $this->assertNotNull($sample);
        $this->assertTrue($sample->isSensitive());
        $this->assertNull($sample->client_id);
        $this->assertEquals('Site A', $sample->collection_site);
        $this->assertEquals('Dr. Bob', $sample->collected_by);
        $this->assertNull($sample->notes);
        $this->assertEquals('collected', $sample->status);

        $response->assertRedirect(route('samples.index'));
        $response->assertSessionHas('success');
    }

    public function test_staff_takes_403_on_show_and_edit_for_sensitive()
    {
        $sample = Sample::create([
            'code' => '0001/26',
            'code_progressive' => 1,
            'code_year' => 26,
            'client_id' => null,
            'sample_type_id' => $this->sensitiveType->id,
            'sample_type' => 'Legacy Type',
            'collection_site' => null,
            'collected_at' => now(),
            'status' => 'collected',
            'created_by' => $this->staff->id
        ]);

        $this->actingAs($this->staff)->get(route('samples.show', $sample))->assertStatus(403);
        $this->actingAs($this->staff)->get(route('samples.edit', $sample))->assertStatus(403);
    }

    public function test_admin_opens_incomplete_sensitive_without_crash()
    {
        $sample = Sample::create([
            'code' => '0002/26',
            'code_progressive' => 2,
            'code_year' => 26,
            'client_id' => null, // INCOMPLETE
            'sample_type_id' => $this->sensitiveType->id,
            'sample_type' => 'Legacy Type',
            'collection_site' => null,
            'collected_at' => now(),
            'status' => 'collected',
            'created_by' => $this->staff->id
        ]);

        $this->actingAs($this->admin)->get(route('samples.show', $sample))->assertStatus(200);
        $this->actingAs($this->admin)->get(route('samples.edit', $sample))->assertStatus(200);
    }

    public function test_staff_upload_and_download_files_on_sensitive_are_blocked()
    {
        $sample = Sample::create([
            'code' => '0003/26',
            'code_progressive' => 3,
            'code_year' => 26,
            'client_id' => null,
            'sample_type_id' => $this->sensitiveType->id,
            'sample_type' => 'Legacy Type',
            'collection_site' => null,
            'collected_at' => now(),
            'status' => 'collected',
            'created_by' => $this->staff->id
        ]);

        $fileData = [
            'file' => \Illuminate\Http\UploadedFile::fake()->create('test.pdf', 100),
            'type' => 'attachment',
        ];

        // Store block
        $this->actingAs($this->staff)
             ->post(route('samples.files.store', $sample), $fileData)
             ->assertStatus(403);

        // Bypass create directly to test download block
        $sampleFile = $sample->files()->create([
            'original_name' => 'test.pdf',
            'path' => 'fake/path.pdf',
            'type' => 'attachment',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size' => 100,
            'uploaded_by' => $this->admin->id
        ]);

        $this->actingAs($this->staff)
             ->get(route('samples.files.download', [$sample, $sampleFile]))
             ->assertStatus(403);
    }

    public function test_admin_accept_and_complete_are_blocked_until_complete()
    {
        $sample = Sample::create([
            'code' => '0004/26',
            'code_progressive' => 4,
            'code_year' => 26,
            'client_id' => null, // INCOMPLETE
            'sample_type_id' => $this->sensitiveType->id,
            'sample_type' => 'Legacy Type',
            'collection_site' => null,
            'collected_at' => now(),
            'status' => 'collected',
            'created_by' => $this->staff->id
        ]);

        $this->assertTrue($sample->isSensitiveIncomplete());

        // Admin cannot accept it while incomplete
        $this->actingAs($this->admin)
             ->patch(route('samples.accept', $sample))
             ->assertStatus(403);
             
        $sample->update(['status' => 'accepted']); // forced to test complete
        
        $this->actingAs($this->admin)
             ->patch(route('samples.complete', $sample))
             ->assertStatus(403);

        // Assign client to make it complete
        $client = Client::create([
            'company_name' => 'ACME Corp',
            'type' => 'company',
            'created_by' => $this->admin->id
        ]);
        $sample->update(['client_id' => $client->id]);

        $this->assertFalse($sample->isSensitiveIncomplete());

        // Now Admin can complete it
        $this->actingAs($this->admin)
             ->patch(route('samples.complete', $sample))
             ->assertRedirect();
    }
}
