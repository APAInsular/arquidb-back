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
        $permissions = [
            //people
            'get people',
            'insert people',
            'update people',
            'delete people',
            //collegiates
            'get collegiates',
            'insert collegiates',
            'update collegiates',
            'delete collegiates',
            //clients
            'get clients',
            'insert clients',
            'update clients',
            'delete clients',
            //expedients
            'get expedients',
            'insert expedients',
            'update expedients',
            'delete expedients',
            //phases
            'get phases',
            'insert phases',
            'update phases',
            'delete phases',
            //documents
            'get documents',
            'insert documents',
            'update documents',
            'delete documents',
            'sign documents',
            //records
            'get records',
            'insert records',
            'update records',
            'delete records',
            //phones
            'get phones',
            'insert phones',
            'update phones',
            'delete phones',
            //addresses
            'get addresses',
            'insert addresses',
            'update addresses',
            'delete addresses',
            //emails
            'get emails',
            'insert emails',
            'update emails',
            'delete emails',
            //expedient_person
            'get expedient_person',
            'insert expedient_person',
            'update expedient_person',
            'delete expedient_person'
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        $getPermissions = array_filter($permissions, function ($permission) {
            return strpos($permission, 'get ') === 0; // Verifica si el string comienza con 'get '
        });

        // 3. Assign permission to a role
        $superAdmin->givePermissionTo(Permission::all());
        $visor->givePermissionTo(Permission::all());
        $user->syncPermissions($getPermissions);
    }
}
