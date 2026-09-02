<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = \App\Models\User::firstOrCreate(
            ['email' => 'info@sodanoconsulting.it'],
            [
                'name' => 'Sodano Consulting Admin',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'is_active' => true,
            ]
        );

        // Assicuriamoci che il ruolo esista
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        
        // Assegniamo il ruolo all'utente
        if (!$admin->hasRole('admin')) {
            $admin->assignRole($role);
        }
    }
}
