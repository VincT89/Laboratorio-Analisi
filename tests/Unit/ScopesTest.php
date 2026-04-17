<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\Sample;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScopesTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_active_and_archived_scopes()
    {
        // 1 cliente attivo, 1 archiviato
        $activeClient = Client::create(['type' => 'company', 'company_name' => 'Active Company', 'archived' => false]);
        $archivedClient = Client::create(['type' => 'company', 'company_name' => 'Archived Company', 'archived' => true, 'archived_at' => now()]);

        $activeClients = Client::active()->get();
        $this->assertCount(1, $activeClients);
        $this->assertEquals('Active Company', $activeClients->first()->company_name);

        $archivedClients = Client::archived()->get();
        $this->assertCount(1, $archivedClients);
        $this->assertEquals('Archived Company', $archivedClients->first()->company_name);
    }

    public function test_sample_active_and_archived_scopes()
    {
        $client = Client::create(['type' => 'company', 'company_name' => 'Test']);

        $user = User::factory()->create();

        $activeSample = Sample::create([
            'client_id' => $client->id,
            'code' => 'L-1',
            'sample_type' => 'T',
            'collection_site' => 'S',
            'collected_by' => 'M',
            'collected_at' => now(),
            'created_by' => $user->id,
            'archived' => false
        ]);

        $archivedSample = Sample::create([
            'client_id' => $client->id,
            'code' => 'L-2',
            'sample_type' => 'T',
            'collection_site' => 'S',
            'collected_by' => 'M',
            'collected_at' => now(),
            'created_by' => $user->id,
            'archived' => true,
            'archived_at' => now()
        ]);

        $activeSamples = Sample::active()->get();
        $this->assertCount(1, $activeSamples);
        $this->assertEquals('L-1', $activeSamples->first()->code);

        $archivedSamples = Sample::archived()->get();
        $this->assertCount(1, $archivedSamples);
        $this->assertEquals('L-2', $archivedSamples->first()->code);
    }
}
