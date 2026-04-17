<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Cancella i clienti esistenti prima di reinserirli
        DB::table('clients')->delete();

        $now = Carbon::now();

        $clients = [
            [
                'company_name' => 'Levante Analisi S.r.l.',
                'first_name' => null,
                'last_name' => null,
                'type' => 'company',
                'email' => 'info@levante-analisi.ba.it',
                'phone' => '080 1234567',
                'pec' => 'levante@pec.it',
                'address' => 'Via Sparano da Bari, 45',
                'city' => 'Bari',
                'province' => 'BA',
                'postal_code' => '70121',
                'tax_code' => '01234567890',
                'vat_number' => 'IT01234567890',
                'sdi_code' => 'M5UXCR1',
                'notes' => 'Laboratorio per analisi cliniche convenzionato.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'company_name' => 'AgriFood Meridionale S.p.A.',
                'first_name' => null,
                'last_name' => null,
                'type' => 'company',
                'email' => 'laboratorio@agrifood.ba.it',
                'phone' => '080 9876543',
                'pec' => 'agrifood@legalmail.it',
                'address' => 'Zona Industriale Mungivacca',
                'city' => 'Bari',
                'province' => 'BA',
                'postal_code' => '70126',
                'tax_code' => '09876543211',
                'vat_number' => 'IT09876543211',
                'sdi_code' => 'USAL8PV',
                'notes' => 'Polo ittico di Bari, controlli settimanali.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'company_name' => '',
                'first_name' => 'Nicola',
                'last_name' => 'Cassano',
                'type' => 'individual',
                'email' => 'nicola.cassano@gmail.com',
                'phone' => '333 1122334',
                'pec' => null,
                'address' => 'Corso Vittorio Emanuele II, 12',
                'city' => 'Bari',
                'province' => 'BA',
                'postal_code' => '70122',
                'tax_code' => 'CSSNCL80A01A662Z',
                'vat_number' => null,
                'sdi_code' => '0000000',
                'notes' => 'Ingegnere ambientale indipendente.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'company_name' => 'Edilizia Pugliese Consorzio',
                'first_name' => null,
                'last_name' => null,
                'type' => 'company',
                'email' => 'direzione@ediliziapugliese.it',
                'phone' => '080 5556667',
                'pec' => 'edilizia@pec.puglia.it',
                'address' => 'Viale della Repubblica 101',
                'city' => 'Bari',
                'province' => 'BA',
                'postal_code' => '70125',
                'tax_code' => '11223344556',
                'vat_number' => 'IT11223344556',
                'sdi_code' => 'KRR89Q1',
                'notes' => 'Controllo amianto ed emissioni cantieri edili.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'company_name' => 'Clinica San Nicola',
                'first_name' => null,
                'last_name' => null,
                'type' => 'company',
                'email' => 'segreteria@clinicasannicola.ba.it',
                'phone' => '080 4455668',
                'pec' => 'sannicola@pec.clinica.it',
                'address' => 'Piazza Giulio Cesare 10',
                'city' => 'Bari',
                'province' => 'BA',
                'postal_code' => '70124',
                'tax_code' => '99887766554',
                'vat_number' => 'IT99887766554',
                'sdi_code' => 'A4707H7',
                'notes' => 'Policlinico di Bari, analisi conto terzi.',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];

        DB::table('clients')->insert($clients);
    }
}
