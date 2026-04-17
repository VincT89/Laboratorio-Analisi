<?php

namespace Database\Seeders;

use App\Models\SampleType;
use Illuminate\Database\Seeder;

class SampleTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Acque', 'is_active' => true, 'is_sensitive' => false],
            ['name' => 'Aria', 'is_active' => true, 'is_sensitive' => false],
            ['name' => 'Terreni', 'is_active' => true, 'is_sensitive' => false],
            ['name' => 'Alimenti', 'is_active' => true, 'is_sensitive' => false],
            ['name' => 'Microbiologico', 'is_active' => true, 'is_sensitive' => false],

            ['name' => 'Tossicologico', 'is_active' => true, 'is_sensitive' => true],
            ['name' => 'Biologico Riservato', 'is_active' => true, 'is_sensitive' => true],
        ];

        foreach ($types as $type) {
            SampleType::updateOrCreate(
                ['name' => $type['name']],
                [
                    'is_active' => $type['is_active'],
                    'is_sensitive' => $type['is_sensitive'],
                ]
            );
        }

        $this->command?->info('Tipi campione creati correttamente.');
    }
}
