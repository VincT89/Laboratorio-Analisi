<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Sample;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class SampleCodeGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        Permission::create(['name' => 'create samples']);
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        return $admin;
    }

    public function test_sample_code_is_generated_correctly_and_increments()
    {
        $admin = $this->createAdmin();
        $client = Client::create(['type' => 'company', 'company_name' => 'Factory Srl']);

        $type = \App\Models\SampleType::factory()->create();

        $sampleData = [
            'creation_mode' => 'standard',
            'client_id' => $client->id,
            'collected_at' => now()->format('Y-m-d'),
            'sample_type_id' => $type->id,
            'collected_by' => 'User A'
        ];

        // Creiamo il primo campione
        $this->actingAs($admin)->post(route('samples.store'), $sampleData);
        $sample1 = Sample::latest('id')->first();
        
        $year = now()->format('y');
        $this->assertEquals("0001/{$year}", $sample1->code);

        // Creiamo il secondo campione
        $this->actingAs($admin)->post(route('samples.store'), $sampleData);
        $sample2 = Sample::latest('id')->first();
        
        $this->assertEquals("0002/{$year}", $sample2->code);
        
        // Eliminiamo HARD il primo campione
        $sample1->delete();
        
        // Creiamo il terzo campione. Dovrebbe essere 0003, non 0002!
        $this->actingAs($admin)->post(route('samples.store'), $sampleData);
        $sample3 = Sample::latest('id')->first();
        
        $this->assertEquals("0003/{$year}", $sample3->code, "Il sistema è vulnerabile a collisioni dopo hard delete!");
    }
}
