<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Sample;
use App\Models\SampleType;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SampleListPresentationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $staff;

    private Client $client;

    private SampleType $standardType;

    private SampleType $sensitiveType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->staff = User::factory()->create();
        $this->staff->assignRole('staff');

        $this->client = Client::create([
            'company_name' => 'Cliente lista campioni',
            'type' => 'company',
            'created_by' => $this->admin->id,
        ]);

        $this->standardType = SampleType::factory()->create([
            'name' => 'Acque',
            'is_sensitive' => false,
            'is_active' => true,
        ]);

        $this->sensitiveType = SampleType::factory()->create([
            'name' => 'Campione sensibile',
            'is_sensitive' => true,
            'is_active' => true,
        ]);
    }

    public function test_samples_can_be_sorted_by_acceptance_number_across_different_years(): void
    {
        $this->createSample('0002/26', 2, 26, '2026-09-01', 'Secondo campione');
        $this->createSample('0001/27', 1, 27, '2026-08-01', 'Campione anno successivo');
        $this->createSample('0009/25', 9, 25, '2026-09-02', 'Campione anno precedente');

        $this->actingAs($this->admin)
            ->get(route('samples.index', ['sort' => 'acceptance_number', 'direction' => 'asc']))
            ->assertOk()
            ->assertSeeInOrder(['0009/25', '0002/26', '0001/27']);

        $this->actingAs($this->admin)
            ->get(route('samples.index', ['sort' => 'acceptance_number', 'direction' => 'desc']))
            ->assertOk()
            ->assertSeeInOrder(['0001/27', '0002/26', '0009/25']);
    }

    public function test_default_sorting_remains_collection_date_descending(): void
    {
        $this->createSample('0002/26', 2, 26, '2026-09-01', 'Secondo campione');
        $this->createSample('0001/27', 1, 27, '2026-08-01', 'Campione anno successivo');
        $this->createSample('0009/25', 9, 25, '2026-09-02', 'Campione anno precedente');

        $this->actingAs($this->admin)
            ->get(route('samples.index'))
            ->assertOk()
            ->assertSeeInOrder(['0009/25', '0002/26', '0001/27']);
    }

    public function test_notes_are_visible_in_the_sample_list(): void
    {
        $this->createSample(
            '0010/26',
            10,
            26,
            '2026-09-02',
            'Acqua prelevata dal pozzo principale, punto di campionamento nord.'
        );

        $this->actingAs($this->staff)
            ->get(route('samples.index'))
            ->assertOk()
            ->assertSee('Note')
            ->assertSee('Acqua prelevata dal pozzo principale, punto di campionamento nord.');
    }

    public function test_sensitive_sample_notes_are_hidden_from_staff(): void
    {
        $this->createSample(
            '0011/26',
            11,
            26,
            '2026-09-02',
            'Informazione riservata che non deve comparire nella lista.',
            $this->sensitiveType
        );

        $this->actingAs($this->staff)
            ->get(route('samples.index'))
            ->assertOk()
            ->assertSee('0011/26')
            ->assertDontSee('Informazione riservata che non deve comparire nella lista.');
    }

    public function test_invalid_sorting_parameters_fall_back_to_the_default_order(): void
    {
        $this->createSample('0001/26', 1, 26, '2026-09-01', 'Campione meno recente');
        $this->createSample('0002/26', 2, 26, '2026-09-02', 'Campione più recente');

        $this->actingAs($this->admin)
            ->get(route('samples.index', ['sort' => 'not_allowed', 'direction' => 'sideways']))
            ->assertOk()
            ->assertSeeInOrder(['0002/26', '0001/26']);
    }

    private function createSample(
        string $code,
        int $progressive,
        int $year,
        string $collectedAt,
        string $notes,
        ?SampleType $sampleType = null
    ): Sample {
        $sampleType ??= $this->standardType;

        return Sample::create([
            'code' => $code,
            'code_progressive' => $progressive,
            'code_year' => $year,
            'client_id' => $this->client->id,
            'sample_type_id' => $sampleType->id,
            'sample_type' => $sampleType->name,
            'collection_site' => 'Punto di prelievo',
            'collected_by' => 'Tecnico incaricato',
            'collected_at' => $collectedAt,
            'status' => 'collected',
            'notes' => $notes,
            'created_by' => $this->admin->id,
        ]);
    }
}
