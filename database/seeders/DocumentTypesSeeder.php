<?php

namespace Database\Seeders;

use App\Models\DocumentType;
use Illuminate\Database\Seeder;

class DocumentTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Referto / Rapporto di Prova', 'code' => 'report', 'sort_order' => 10],
            ['name' => 'Allegato Generico', 'code' => 'attachment', 'sort_order' => 20],
            ['name' => 'Certificato', 'code' => 'prescription', 'sort_order' => 30],
            ['name' => 'Referto Revisionato', 'code' => 'revised_report', 'sort_order' => 40],
        ];

        foreach ($types as $type) {
            DocumentType::firstOrCreate(
                ['code' => $type['code']],
                [
                    'name' => $type['name'],
                    'is_active' => true,
                    'sort_order' => $type['sort_order'],
                ]
            );
        }
    }
}
