<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            UsersSeeder::class,
            SampleTypesSeeder::class,
            ContainerTypesSeeder::class,
            ClientsSeeder::class,
            SamplesSeeder::class,
        ]);
    }
}