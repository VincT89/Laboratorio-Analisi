<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ClientTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup ruoli e permessi per il test
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        
        Permission::create(['name' => 'view clients']);
        Permission::create(['name' => 'create clients']);
        Permission::create(['name' => 'edit clients']);
        Permission::create(['name' => 'archive clients']);
        Permission::create(['name' => 'delete clients']);
        
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        $staffRole = Role::create(['name' => 'staff']);
        $staffRole->givePermissionTo(['view clients', 'create clients', 'edit clients', 'archive clients']);
    }

    private function createAdmin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        return $admin;
    }

    private function createStaff(): User
    {
        $staff = User::factory()->create();
        $staff->assignRole('staff');
        return $staff;
    }

    public function test_admin_can_view_clients_index()
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('clients.index'));

        $response->assertStatus(200);
        $response->assertViewIs('clients.index');
    }

    public function test_unauthenticated_user_cannot_view_clients()
    {
        $response = $this->get(route('clients.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_staff_can_create_client_via_standard_form()
    {
        $staff = $this->createStaff();

        $clientData = [
            'type' => 'company',
            'company_name' => 'Test Company Srl',
            'email' => 'test@company.com',
        ];

        $response = $this->actingAs($staff)->post(route('clients.store'), $clientData);

        $this->assertDatabaseHas('clients', [
            'company_name' => 'Test Company Srl',
            'email' => 'test@company.com',
            'created_by' => $staff->id
        ]);

        $client = Client::where('company_name', 'Test Company Srl')->first();
        $response->assertRedirect(route('clients.show', $client));
    }

    public function test_staff_can_create_client_via_ajax_modal()
    {
        $staff = $this->createStaff();

        $clientData = [
            'type' => 'company',
            'company_name' => 'Ajax Company SpA',
            'vat_number' => '12345678901'
        ];

        // Simuliamo una richiesta AJAX/Fetch impostando l'header HTTP_ACCEPT a application/json
        $response = $this->actingAs($staff)->postJson(route('clients.store'), $clientData);

        $response->assertStatus(200);
        
        // Verifica che la risposta sia il JSON corretto
        $response->assertJson([
            'success' => true,
            'message' => 'Cliente creato correttamente.',
        ]);
        
        $response->assertJsonStructure(['client' => ['id', 'text']]);

        $this->assertDatabaseHas('clients', [
            'company_name' => 'Ajax Company SpA'
        ]);
    }

    public function test_staff_cannot_delete_client_but_admin_can()
    {
        $staff = $this->createStaff();
        $admin = $this->createAdmin();
        $client = Client::create([
            'type' => 'company',
            'company_name' => 'To Be Deleted Srl',
        ]);

        // Staff prova a cancellare
        $response = $this->actingAs($staff)->delete(route('clients.destroy', $client));
        $response->assertStatus(403); // Forbidden

        $this->assertDatabaseHas('clients', ['id' => $client->id]);

        // Admin prova a cancellare
        $response = $this->actingAs($admin)->delete(route('clients.destroy', $client));
        $response->assertRedirect(route('clients.index'));

        $this->assertDatabaseMissing('clients', ['id' => $client->id]);
    }
}
