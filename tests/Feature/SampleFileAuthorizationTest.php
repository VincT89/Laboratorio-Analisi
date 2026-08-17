<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\DocumentType;
use App\Models\Sample;
use App\Models\SampleFile;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
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

    private DocumentType $documentType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->staff = User::factory()->create();
        $this->staff->assignRole('staff');

        $client = Client::create(['type' => 'company', 'company_name' => 'Factory Srl']);

        $this->sampleA = Sample::create(['client_id' => $client->id, 'sample_type' => 'A', 'collection_site' => 'Site', 'collected_by' => 'User', 'collected_at' => now(), 'code' => 'LAB-2026-00001', 'created_by' => $this->admin->id]);
        $this->sampleB = Sample::create(['client_id' => $client->id, 'sample_type' => 'B', 'collection_site' => 'Site', 'collected_by' => 'User', 'collected_at' => now(), 'code' => 'LAB-2026-00002', 'created_by' => $this->admin->id]);
        $this->documentType = DocumentType::where('code', 'report')->firstOrFail();

        Storage::fake('private');
    }

    public function test_cannot_access_file_belonging_to_another_sample()
    {
        // Creiamo un file per il sample A
        $fileA = SampleFile::create([
            'sample_id' => $this->sampleA->id,
            'document_type_id' => $this->documentType->id,
            'original_name' => 'test.pdf',
            'type' => 'report',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size' => 100,
            'path' => 'fake/path.pdf',
            'uploaded_by' => $this->admin->id,
        ]);

        // Proviamo a scaricarlo passando l'id del sample B (Cross-Reference bug)
        $response = $this->actingAs($this->admin)->get(route('samples.files.download', [
            'sample' => $this->sampleB->id,
            'sampleFile' => $fileA->id,
        ]));

        $response->assertStatus(404);
    }

    public function test_cannot_upload_file_to_archived_sample()
    {
        $this->sampleA->update(['archived' => true]);

        $file = UploadedFile::fake()->create('report.pdf', 100);

        $response = $this->actingAs($this->admin)->post(route('samples.files.store', $this->sampleA), [
            'file' => $file,
            'document_type_id' => $this->documentType->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_upload_form_contains_file_type_dropdown()
    {
        $response = $this->actingAs($this->admin)->get(route('samples.show', $this->sampleA));

        $response->assertOk();
        $response->assertSee('name="document_type_id"', false);

        foreach (DocumentType::active()->pluck('name') as $label) {
            $response->assertSee($label);
        }
    }

    public function test_unknown_file_type_is_rejected()
    {
        $response = $this->actingAs($this->admin)->post(route('samples.files.store', $this->sampleA), [
            'file' => UploadedFile::fake()->create('report.pdf', 100),
            'document_type_id' => 999999,
        ]);

        $response->assertSessionHasErrors('document_type_id');
        $this->assertDatabaseCount('sample_files', 0);
    }

    public function test_inactive_file_type_is_not_available_for_upload()
    {
        $inactiveType = DocumentType::create([
            'name' => 'Documento Disattivato',
            'is_active' => false,
        ]);

        $this->actingAs($this->admin)
            ->get(route('samples.show', $this->sampleA))
            ->assertDontSee($inactiveType->name);

        $response = $this->actingAs($this->admin)->post(route('samples.files.store', $this->sampleA), [
            'file' => UploadedFile::fake()->create('report.pdf', 100),
            'document_type_id' => $inactiveType->id,
        ]);

        $response->assertSessionHasErrors('document_type_id');
    }

    public function test_custom_document_type_can_be_used_for_upload()
    {
        $customType = DocumentType::create([
            'name' => 'Verbale Fotografico',
            'is_active' => true,
            'sort_order' => 5,
        ]);

        $response = $this->actingAs($this->admin)->post(route('samples.files.store', $this->sampleA), [
            'file' => UploadedFile::fake()->create('foto.pdf', 100),
            'document_type_id' => $customType->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('sample_files', [
            'sample_id' => $this->sampleA->id,
            'document_type_id' => $customType->id,
            'type' => $customType->code,
        ]);
    }

    public function test_existing_file_keeps_its_document_type_after_rename_and_deactivation()
    {
        $file = SampleFile::create([
            'sample_id' => $this->sampleA->id,
            'document_type_id' => $this->documentType->id,
            'original_name' => 'storico.pdf',
            'type' => $this->documentType->code,
            'path' => 'fake/storico.pdf',
            'uploaded_by' => $this->admin->id,
        ]);

        $this->documentType->update([
            'name' => 'Referto Storico Rinominato',
            'is_active' => false,
        ]);

        $this->assertSame('Referto Storico Rinominato', $file->fresh()->type_label);

        $this->actingAs($this->admin)
            ->get(route('samples.show', $this->sampleA))
            ->assertSee('Referto Storico Rinominato');
    }

    public function test_cannot_download_archived_file()
    {
        $fileA = SampleFile::create([
            'sample_id' => $this->sampleA->id,
            'document_type_id' => $this->documentType->id,
            'path' => 'fake/path.pdf',
            'original_name' => 'test.pdf',
            'type' => 'report',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size' => 100,
            'archived' => true,
            'uploaded_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->staff)->get(route('samples.files.download', [
            'sample' => $this->sampleA->id,
            'sampleFile' => $fileA->id,
        ]));

        $response->assertStatus(403);
    }
}
