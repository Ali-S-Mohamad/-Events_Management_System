<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = Permission::pluck('id', 'id')->all();

        $admin = Role::create(['name' => 'admin']);
        $organizer = Role::create(['name' => 'organizer']);
        $guest = Role::create(['name' => 'guest']);

        $admin->syncPermissions($permissions);
        $organizer->givePermissionTo([
            // Events Permissions
            'create-events',
            'read-events',
            'update-events',
            'delete-events',
        ]);
    }
}