<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class ProductionAdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@aerconsulting.it'],
            [
                'name' => 'Admin AER',
                'password' => 'Admin123',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $admin->syncRoles(['admin']);
    }
}