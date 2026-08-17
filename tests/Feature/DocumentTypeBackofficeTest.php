<?php

namespace Tests\Feature;

use App\Models\DocumentType;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentTypeBackofficeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->staff = User::factory()->create();
        $this->staff->assignRole('staff');
    }

    public function test_admin_can_access_document_types_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('document-types.index'))
            ->assertOk()
            ->assertViewIs('document_types.index');
    }

    public function test_staff_cannot_access_document_types_index(): void
    {
        $this->actingAs($this->staff)
            ->get(route('document-types.index'))
            ->assertForbidden();
    }

    public function test_admin_can_create_document_type(): void
    {
        $response = $this->actingAs($this->admin)->post(route('document-types.store'), [
            'name' => 'Verbale di Campionamento',
            'sort_order' => 15,
        ]);

        $response->assertRedirect(route('document-types.index'));
        $this->assertDatabaseHas('document_types', [
            'name' => 'Verbale di Campionamento',
            'code' => 'verbale_di_campionamento',
            'sort_order' => 15,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_rename_and_reorder_document_type_without_changing_its_code(): void
    {
        $type = DocumentType::create([
            'name' => 'Nome Iniziale',
            'code' => 'stable_code',
            'sort_order' => 50,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->put(route('document-types.update', $type), [
            'name' => 'Nome Aggiornato',
            'sort_order' => 5,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('document-types.index'));
        $this->assertDatabaseHas('document_types', [
            'id' => $type->id,
            'name' => 'Nome Aggiornato',
            'code' => 'stable_code',
            'sort_order' => 5,
            'is_active' => true,
        ]);
    }

    public function test_admin_can_deactivate_and_reactivate_document_type(): void
    {
        $type = DocumentType::create([
            'name' => 'Tipo Temporaneo',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)
            ->patch(route('document-types.deactivate', $type))
            ->assertRedirect();
        $this->assertFalse($type->fresh()->is_active);

        $this->actingAs($this->admin)
            ->patch(route('document-types.activate', $type))
            ->assertRedirect();
        $this->assertTrue($type->fresh()->is_active);
    }
}
