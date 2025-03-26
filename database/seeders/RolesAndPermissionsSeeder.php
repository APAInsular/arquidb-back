<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run()
    {
        // 1. Create roles
        $superAdmin = Role::create(['name' => 'superAdmin']);
        $visor = Role::create(['name' => 'visor']);
        $user = Role::create(['name' => 'user']);

        // 2. Create permissions
        $get = Permission::create(['name' => 'GET ALL']);
        $insert = Permission::create(['name' => 'INSERT ALL']);
        $update = Permission::create(['name' => 'UPDATE ALL']);
        $delete = Permission::create(['name' => 'DELETE ALL']);

        $permissions = [
            'get persons'
        ];

        // 3. Assign permission to a role
        $superAdmin->givePermissionTo(Permission::all());
        $visor->givePermissionTo(Permission::all());
        $user->givePermissionTo($get);
    }
}
