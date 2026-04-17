<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Admin AER',
                'email' => 'admin@aerconsulting.it',
                'password' => 'password',
                'role' => 'admin',
            ],
            [
                'name' => 'Staff Bari 1',
                'email' => 'staff1@aerconsulting.it',
                'password' => 'password',
                'role' => 'staff',
            ],
            [
                'name' => 'Staff Bari 2',
                'email' => 'staff2@aerconsulting.it',
                'password' => 'password',
                'role' => 'staff',
            ],
            [
                'name' => 'Staff Bari 3',
                'email' => 'staff3@aerconsulting.it',
                'password' => 'password',
                'role' => 'staff',
            ],
        ];

        foreach ($users as $data) {
            $role = $data['role'];
            unset($data['role']);

            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => $data['password'],
                    'is_active' => true,
                ]
            );

            $user->syncRoles([$role]);
        }

        $this->command?->info('Utenti demo creati correttamente.');
        $this->command?->line('Admin: admin@aerconsulting.it / password');
        $this->command?->line('Staff: staff1@aerconsulting.it / password');
        $this->command?->line('Staff: staff2@aerconsulting.it / password');
        $this->command?->line('Staff: staff3@aerconsulting.it / password');
    }
}
