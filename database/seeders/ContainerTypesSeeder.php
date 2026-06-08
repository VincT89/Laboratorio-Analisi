<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ContainerType;

class ContainerTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Barattolo PP 100ml', 'is_active' => true],
            ['name' => 'Barattolo PP 250ml', 'is_active' => true],
            ['name' => 'Barattolo PP 500ml', 'is_active' => true],
            ['name' => 'Bottiglia PE 500ml', 'is_active' => true],
            ['name' => 'Bottiglia PE 1000ml', 'is_active' => true],
            ['name' => 'Bottiglia Vetro scuro 1000ml', 'is_active' => true],
            ['name' => 'Sacca Tedlar', 'is_active' => true],
            ['name' => 'Fiala Carbone Attivo', 'is_active' => true],
            ['name' => 'Fiala Gel di Silice', 'is_active' => true],
            ['name' => 'Filtro in fibra di vetro', 'is_active' => true],
            ['name' => 'Altro', 'is_active' => true],
        ];

        foreach ($types as $type) {
            ContainerType::updateOrCreate(
                ['name' => $type['name']],
                [
                    'slug' => \Illuminate\Support\Str::slug($type['name']),
                    'is_active' => $type['is_active']
                ]
            );
        }
    }
}
