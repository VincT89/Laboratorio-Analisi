<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClientsSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@aerconsulting.it')->firstOrFail();

        $cities = [
            ['city' => 'Bari', 'province' => 'BA', 'postal_code' => '70121'],
            ['city' => 'Modugno', 'province' => 'BA', 'postal_code' => '70026'],
            ['city' => 'Bitonto', 'province' => 'BA', 'postal_code' => '70032'],
            ['city' => 'Molfetta', 'province' => 'BA', 'postal_code' => '70056'],
            ['city' => 'Giovinazzo', 'province' => 'BA', 'postal_code' => '70054'],
            ['city' => 'Triggiano', 'province' => 'BA', 'postal_code' => '70019'],
            ['city' => 'Noicattaro', 'province' => 'BA', 'postal_code' => '70016'],
            ['city' => 'Rutigliano', 'province' => 'BA', 'postal_code' => '70018'],
            ['city' => 'Conversano', 'province' => 'BA', 'postal_code' => '70014'],
            ['city' => 'Monopoli', 'province' => 'BA', 'postal_code' => '70043'],
        ];

        $companies = [
            'Levante Analisi S.r.l.',
            'AgriFood Meridionale S.p.A.',
            'Clinica San Nicola',
            'Edilizia Pugliese Consorzio',
            'BioTest Adriatica S.r.l.',
            'Caseificio Murgia S.r.l.',
            'Acquedotto Locale Servizi S.p.A.',
            'Polo Medico Sud S.r.l.',
            'Cantine Terra di Bari',
            'Logistica Industriale Barese S.r.l.',
            'Laboratori Apulia Check S.r.l.',
            'Studio Ambiente Puglia',
        ];

        foreach ($companies as $index => $company) {
            $place = $cities[$index % count($cities)];

            Client::updateOrCreate(
                ['company_name' => $company],
                [
                    'first_name' => null,
                    'last_name' => null,
                    'type' => 'company',
                    'email' => fake()->unique()->companyEmail(),
                    'phone' => fake()->phoneNumber(),
                    'pec' => fake()->optional()->safeEmail(),
                    'address' => fake()->streetAddress(),
                    'city' => $place['city'],
                    'province' => $place['province'],
                    'postal_code' => $place['postal_code'],
                    'country' => 'Italia',
                    'tax_code' => strtoupper(fake()->bothify('###########')),
                    'vat_number' => 'IT' . fake()->numerify('###########'),
                    'sdi_code' => strtoupper(fake()->bothify('????###')),
                    'notes' => fake()->sentence(),
                    'archived' => false,
                    'archived_at' => null,
                    'archived_by' => null,
                    'created_by' => $admin->id,
                ]
            );
        }

        $individuals = [
            ['first_name' => 'Nicola', 'last_name' => 'Cassano'],
            ['first_name' => 'Francesca', 'last_name' => 'Lorusso'],
            ['first_name' => 'Michele', 'last_name' => 'De Santis'],
            ['first_name' => 'Giulia', 'last_name' => 'Ferrante'],
            ['first_name' => 'Vito', 'last_name' => 'Bellomo'],
            ['first_name' => 'Angela', 'last_name' => 'Mastronardi'],
        ];

        foreach ($individuals as $index => $person) {
            $place = $cities[($index + 3) % count($cities)];
            $fullName = $person['first_name'] . ' ' . $person['last_name'];

            Client::updateOrCreate(
                ['email' => fake()->unique()->safeEmail()],
                [
                    'company_name' => $fullName,
                    'first_name' => $person['first_name'],
                    'last_name' => $person['last_name'],
                    'type' => 'individual',
                    'phone' => fake()->phoneNumber(),
                    'pec' => null,
                    'address' => fake()->streetAddress(),
                    'city' => $place['city'],
                    'province' => $place['province'],
                    'postal_code' => $place['postal_code'],
                    'country' => 'Italia',
                    'tax_code' => strtoupper(fake()->bothify('??????##?##?###?')),
                    'vat_number' => null,
                    'sdi_code' => '0000000',
                    'notes' => fake()->sentence(),
                    'archived' => false,
                    'archived_at' => null,
                    'archived_by' => null,
                    'created_by' => $admin->id,
                ]
            );
        }

        $this->command?->info('Clienti demo Bari e provincia creati correttamente.');
    }
}
