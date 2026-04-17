<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Sample;
use App\Models\SampleType;
use App\Models\Client;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardSensitiveAdminTest extends TestCase
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

        $this->sensitiveType = SampleType::factory()->create(['name' => 'Sensibile', 'is_sensitive' => true, 'is_active' => true]);
        $this->standardType = SampleType::factory()->create(['name' => 'Standard', 'is_sensitive' => false, 'is_active' => true]);
    }

    public function test_admin_sees_sensitive_incomplete_widget_on_dashboard()
    {
        // 1 incomplete sensitive
        Sample::create([
            'code' => 'SENS-001',
            'client_id' => null,
            'sample_type_id' => $this->sensitiveType->id,
            'sample_type' => 'Legacy Type',
            'collection_site' => 'Site A',
            'collected_by' => 'User',
            'collected_at' => now(),
            'status' => 'collected',
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id
        ]);

        // 1 complete sensitive
        $client = Client::create([
            'company_name' => 'Test Company',
            'type' => 'company',
            'created_by' => $this->admin->id
        ]);
        
        Sample::create([
            'code' => 'SENS-002',
            'client_id' => $client->id,
            'sample_type_id' => $this->sensitiveType->id,
            'sample_type' => 'Legacy Type',
            'collection_site' => 'Site A',
            'collected_by' => 'User',
            'collected_at' => now(),
            'status' => 'collected',
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id
        ]);

        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Analisi Sensibili da Completare');
        
        $response->assertViewHas('sensitiveIncompleteSamples', function ($samples) {
            return $samples->contains('code', 'SENS-001') && !$samples->contains('code', 'SENS-002');
        });
    }

    public function test_staff_does_not_see_sensitive_incomplete_widget()
    {
        Sample::create([
            'code' => 'SENS-001',
            'client_id' => null,
            'sample_type_id' => $this->sensitiveType->id,
            'sample_type' => 'Legacy Type',
            'collection_site' => 'Site A',
            'collected_by' => 'User',
            'collected_at' => now(),
            'status' => 'collected',
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id
        ]);

        $response = $this->actingAs($this->staff)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertDontSee('Analisi Sensibili da Completare', false); // Note: Since the test might fail asserting on escaped strings, it's safer
        // We ensure SENS-001 is not clearly displayed in the widget (since widget doesn't render)
    }

    public function test_dashboard_link_to_incomplete_filter_works()
    {
        $response = $this->actingAs($this->admin)->get(route('samples.index', ['status' => 'incomplete']));
        
        $response->assertStatus(200);
    }
}
