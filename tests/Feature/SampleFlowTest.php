<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Sample;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SampleFlowTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup ruoli e permessi per il test
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        Permission::create(['name' => 'view samples']);
        Permission::create(['name' => 'create samples']);
        Permission::create(['name' => 'edit samples']);
        Permission::create(['name' => 'archive samples']);
        Permission::create(['name' => 'delete samples']);
        Permission::create(['name' => 'view clients']);
        Permission::create(['name' => 'archive clients']);
        
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $staffRole = Role::create(['name' => 'staff']);
        $staffRole->givePermissionTo([
            'view samples', 
            'create samples', 
            'edit samples', 
            'archive samples',
            'view clients',
            'archive clients'
        ]);
    }

    private function createStaff(): User
    {
        $staff = User::factory()->create();
        $staff->assignRole('staff');
        return $staff;
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        return $admin;
    }

    public function test_sample_status_transitions()
    {
        $staff = $this->createStaff();
        $client = Client::create(['type' => 'company', 'company_name' => 'Test Srl']);

        // 1. Creazione (collected)
        $sampleData = [
            'creation_mode' => 'standard',
            'client_id' => $client->id,
            'sample_type_id' => \App\Models\SampleType::factory()->create(['is_sensitive' => false])->id,
            'collection_site' => 'Pozzo 1',
            'collected_by' => 'Mario',
            'collected_at' => now()->format('Y-m-d')
        ];

        $response = $this->actingAs($staff)->post(route('samples.store'), $sampleData);

        $sample = Sample::first();
        if (!$sample) {
            $response->dumpSession();
            $this->fail('Sample was not created. Validation errors likely.');
        }
        $this->assertEquals('collected', $sample->status);
        $this->assertNull($sample->accepted_at);

        // 2. Modifica stato accettazione (diventa accepted)
        $response = $this->actingAs($staff)->patch(route('samples.accept', $sample));
        $response->assertRedirect();
        
        $sample->refresh();
        $this->assertEquals('accepted', $sample->status);
        $this->assertNotNull($sample->accepted_at);

        // 3. Completamento (diventa completed via patch specifica)
        $response = $this->actingAs($staff)->patch(route('samples.complete', $sample));
        $response->assertRedirect();
        
        $sample->refresh();
        $this->assertEquals('completed', $sample->status);
    }

    public function test_cascading_client_archive_archives_samples()
    {
        $admin = $this->createAdmin();
        $client = Client::create(['type' => 'company', 'company_name' => 'Test Srl']);
        
        $sample = Sample::create([
            'client_id' => $client->id,
            'code' => 'LAB-2026-00001',
            'sample_type_id' => \App\Models\SampleType::factory()->create()->id,
            'sample_type' => 'Terra',
            'collection_site' => 'Sito A',
            'collected_by' => 'Mario',
            'collected_at' => now(),
            'created_by' => $admin->id
        ]);

        $this->assertFalse((bool)$sample->archived);

        // Archivia cliente
        $response = $this->actingAs($admin)->patch(route('clients.archive', $client));
        $response->assertRedirect();

        $sample->refresh();
        $this->assertTrue((bool)$sample->archived);
        $this->assertNotNull($sample->archived_at);
    }

    public function test_staff_cannot_delete_sample_but_admin_can()
    {
        $staff = $this->createStaff();
        $admin = $this->createAdmin();
        
        $client = Client::create(['type' => 'company', 'company_name' => 'To Be Deleted Srl']);
        $type = \App\Models\SampleType::factory()->create();
        
        $sample = Sample::create([
            'client_id' => $client->id,
            'code' => 'LAB-2026-00002',
            'sample_type_id' => $type->id,
            'sample_type' => 'Aria',
            'collection_site' => 'Sito B',
            'collected_by' => 'Mario',
            'collected_at' => now(),
            'created_by' => $staff->id
        ]);

        // Staff prova a cancellare
        $response = $this->actingAs($staff)->delete(route('samples.destroy', $sample));
        $response->assertStatus(403); // Forbidden

        $this->assertDatabaseHas('samples', ['id' => $sample->id]);

        // Admin prova a cancellare
        $response = $this->actingAs($admin)->delete(route('samples.destroy', $sample));
        $response->assertRedirect(route('samples.index'));

        $this->assertDatabaseMissing('samples', ['id' => $sample->id]);
    }
}
