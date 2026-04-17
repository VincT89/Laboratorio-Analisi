<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Client;
use App\Models\Sample;
use App\Models\SampleType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Permission;

class SampleTypeValidationTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;
    protected User $admin;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $this->staff = User::factory()->create();
        $this->staff->assignRole('staff');

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->client = Client::create([
            'type' => 'company',
            'company_name' => 'Test Company',
            'created_by' => $this->admin->id,
            'archived' => false,
        ]);
    }

    public function test_cannot_create_sample_with_inactive_sample_type()
    {
        $inactiveType = SampleType::factory()->create(['is_active' => false]);

        $response = $this->actingAs($this->admin)->post(route('samples.store'), [
            'client_id' => $this->client->id,
            'collected_at' => now()->format('Y-m-d'),
            'sample_type_id' => $inactiveType->id,
            'collection_site' => 'Test',
            'collected_by' => 'User',
        ]);

        $response->assertSessionHasErrors(['sample_type_id']);
        $this->assertDatabaseMissing('samples', [
            'sample_type_id' => $inactiveType->id,
        ]);
    }

    public function test_can_update_sample_with_its_own_inactive_sample_type()
    {
        $inactiveType = SampleType::factory()->create(['is_active' => false]);
        
        $sample = Sample::create([
            'code' => 'TEST-002',
            'client_id' => $this->client->id,
            'sample_type_id' => $inactiveType->id,
            'sample_type' => 'Legacy Name',
            'collection_site' => 'Site A',
            'collected_by' => 'User A',
            'collected_at' => now(),
            'created_by' => $this->admin->id,
            'archived' => false,
        ]);

        $response = $this->actingAs($this->admin)->put(route('samples.update', $sample), [
            'client_id' => $this->client->id,
            'collected_at' => now()->format('Y-m-d'),
            'sample_type_id' => $inactiveType->id, // Keeps the same inactive type
            'collection_site' => 'Updated Site',
            'collected_by' => 'Updated User',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('samples', [
            'id' => $sample->id,
            'collection_site' => 'Updated Site',
            'sample_type_id' => $inactiveType->id,
        ]);
    }

    public function test_cannot_update_sample_by_changing_to_another_inactive_sample_type()
    {
        $activeType = SampleType::factory()->create(['is_active' => true]);
        $inactiveType = SampleType::factory()->create(['is_active' => false]);
        
        $sample = Sample::create([
            'code' => 'TEST-003',
            'client_id' => $this->client->id,
            'sample_type_id' => $activeType->id,
            'sample_type' => 'Active Name',
            'collection_site' => 'Site B',
            'collected_by' => 'User B',
            'collected_at' => now(),
            'created_by' => $this->admin->id,
            'archived' => false,
        ]);

        $response = $this->actingAs($this->admin)->put(route('samples.update', $sample), [
            'client_id' => $this->client->id,
            'collected_at' => now()->format('Y-m-d'),
            'sample_type_id' => $inactiveType->id, // Tries to switch to an inactive type
            'collection_site' => 'Updated Site',
            'collected_by' => 'Updated User',
        ]);

        $response->assertSessionHasErrors(['sample_type_id']);
        $this->assertDatabaseHas('samples', [
            'id' => $sample->id,
            'sample_type_id' => $activeType->id, // Stays the same
        ]);
    }
}
