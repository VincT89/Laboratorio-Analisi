<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Sample;
use App\Models\SampleType;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SensitiveSampleSearchSafetyTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;
    protected SampleType $sensitiveType;
    protected SampleType $standardType;
    protected Client $client;

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
        
        $this->client = Client::create([
            'company_name' => 'Levante Analisi',
            'type' => 'company',
            'created_by' => $this->admin->id
        ]);
    }

    public function test_staff_search_by_client_name_does_not_leak_sensitive_samples()
    {
        // Creiamo un campione standard per questo cliente
        $standardSample = Sample::create([
            'code' => 'LAB-2026-00001',
            'client_id' => $this->client->id,
            'sample_type_id' => $this->standardType->id,
            'sample_type' => $this->standardType->name,
            'collection_site' => 'Sede Principale',
            'collected_by' => 'Staff 1',
            'collected_at' => now(),
            'status' => 'collected',
            'created_by' => $this->staff->id
        ]);

        // Creiamo un campione sensibile, che è stato assegnato all'admin per questo cliente (quindi completato lato anagrafica)
        $sensitiveSample = Sample::create([
            'code' => 'LAB-2026-00002',
            'client_id' => $this->client->id,
            'sample_type_id' => $this->sensitiveType->id,
            'sample_type' => $this->sensitiveType->name,
            'collection_site' => 'Sede Distaccata',
            'collected_by' => 'Staff 1',
            'collected_at' => now(),
            'status' => 'collected',
            'created_by' => $this->staff->id
        ]);

        // Se lo staff cerca "Levante", non deve vedere il campione sensibile
        $response = $this->actingAs($this->staff)->get(route('samples.index', ['search' => 'Levante']));

        $response->assertStatus(200);
        $response->assertSee('LAB-2026-00001'); // Vede lo standard
        $response->assertDontSee('LAB-2026-00002'); // NON vede il sensibile!
    }

    public function test_staff_search_by_code_finds_sensitive_sample_but_it_is_masked()
    {
        // Un campione sensibile che lo staff ha creato o sta cercando
        $sensitiveSample = Sample::create([
            'code' => 'LAB-2026-00003',
            'client_id' => $this->client->id, // assegnato per edge case
            'sample_type_id' => $this->sensitiveType->id,
            'sample_type' => $this->sensitiveType->name,
            'collection_site' => 'Sede Distaccata',
            'collected_by' => 'Staff 1',
            'collected_at' => now(),
            'status' => 'collected',
            'created_by' => $this->staff->id
        ]);

        // Lo staff cerca esplicitamente il codice
        $response = $this->actingAs($this->staff)->get(route('samples.index', ['search' => 'LAB-2026-00003']));

        $response->assertStatus(200);
        $response->assertSee('LAB-2026-00003');
        // Essendo sensibile e non admin, l'anagrafica cliente deve essere mascherata
        $response->assertSee('******', false);
        $response->assertDontSee($this->client->company_name); // Non vede il vero nome del cliente
    }

    public function test_admin_search_finds_standard_and_sensitive_samples_for_client()
    {
        $standardSample = Sample::create([
            'code' => 'LAB-2026-00010',
            'client_id' => $this->client->id,
            'sample_type_id' => $this->standardType->id,
            'sample_type' => $this->standardType->name,
            'collection_site' => 'Sede Principale',
            'collected_by' => 'Staff 1',
            'collected_at' => now(),
            'status' => 'collected',
            'created_by' => $this->staff->id
        ]);

        $sensitiveSample = Sample::create([
            'code' => 'LAB-2026-00011',
            'client_id' => $this->client->id,
            'sample_type_id' => $this->sensitiveType->id,
            'sample_type' => $this->sensitiveType->name,
            'collection_site' => 'Sede Distaccata',
            'collected_by' => 'Staff 1',
            'collected_at' => now(),
            'status' => 'collected',
            'created_by' => $this->staff->id
        ]);

        // L'admin cerca "Levante"
        $response = $this->actingAs($this->admin)->get(route('samples.index', ['search' => 'Levante']));

        $response->assertStatus(200);
        $response->assertSee('LAB-2026-00010'); // Vede lo standard
        $response->assertSee('LAB-2026-00011'); // Vede anche il sensibile
        $response->assertSee($this->client->company_name); // Il nome del cliente non è mascherato per lui
    }
}
