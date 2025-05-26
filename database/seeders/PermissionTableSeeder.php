<?php

namespace Database\Seeders;

use Spatie\Permission\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Events Permissions
            'create-events',
            'read-events',
            'update-events',
            'delete-events',
            // Event Types Permissions
            'create-event-types',
            'read-event-types',
            'update-event-types',
            'delete-event-types',
            // Locations Permissions
            'create-locations',
            'read-locations',
            'update-locations',
            'delete-locations',
            // Reservations Permissions
            'create-reservations',
            'read-reservations',
            'update-reservations',
            'delete-reservations',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }
    }
}
