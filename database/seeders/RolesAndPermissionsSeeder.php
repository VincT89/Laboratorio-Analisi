<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'view clients',
            'create clients',
            'edit clients',
            'archive clients',
            'delete clients',

            'view samples',
            'create samples',
            'edit samples',
            'archive samples',
            'delete samples',

            'view files',
            'upload files',
            'archive files',
            'delete files',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        $staff = Role::firstOrCreate(['name' => 'staff']);
        $staff->syncPermissions([
            'view clients',
            'create clients',
            'edit clients',
            'archive clients',
            'view samples',
            'create samples',
            'edit samples',
            'archive samples',
            'view files',
            'upload files',
            'archive files',
        ]);

        $this->command?->info('Ruoli e permessi creati correttamente.');
    }
}