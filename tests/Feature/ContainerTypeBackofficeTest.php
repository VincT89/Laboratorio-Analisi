<?php

namespace Tests\Feature;

use App\Models\ContainerType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ContainerTypeBackofficeTest extends TestCase
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

    public function test_admin_can_access_container_types_index()
    {
        $response = $this->actingAs($this->admin)->get(route('container-types.index'));
        $response->assertStatus(200);
        $response->assertViewIs('container_types.index');
    }

    public function test_staff_cannot_access_container_types_index()
    {
        $response = $this->actingAs($this->staff)->get(route('container-types.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_create_container_type()
    {
        $response = $this->actingAs($this->admin)->post(route('container-types.store'), [
            'name' => 'Provetta 50ml'
        ]);

        $response->assertRedirect(route('container-types.index'));
        $this->assertDatabaseHas('container_types', [
            'name' => 'Provetta 50ml',
            'slug' => 'provetta-50ml',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_container_type()
    {
        $type = ContainerType::create(['name' => 'Old Name', 'slug' => 'old-name']);

        $response = $this->actingAs($this->admin)->put(route('container-types.update', $type), [
            'name' => 'New Name'
        ]);

        $response->assertRedirect(route('container-types.index'));
        $this->assertDatabaseHas('container_types', [
            'id' => $type->id,
            'name' => 'New Name',
            'slug' => 'new-name'
        ]);
    }

    public function test_admin_can_deactivate_and_activate_container_type()
    {
        $type = ContainerType::create(['name' => 'Test', 'slug' => 'test', 'is_active' => true]);

        $response = $this->actingAs($this->admin)->patch(route('container-types.deactivate', $type));
        $response->assertRedirect();
        $this->assertFalse((bool) $type->fresh()->is_active);

        $response = $this->actingAs($this->admin)->patch(route('container-types.activate', $type));
        $response->assertRedirect();
        $this->assertTrue((bool) $type->fresh()->is_active);
    }
}
