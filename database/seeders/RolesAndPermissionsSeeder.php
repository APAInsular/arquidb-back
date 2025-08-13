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
        // 1. Create roles (idempotent)
        $superAdmin = Role::firstOrCreate(['name' => 'superAdmin']);
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $visor = Role::firstOrCreate(['name' => 'visor']);
        $user = Role::firstOrCreate(['name' => 'user']);

        // 2. Define permission groups
        $userPermissions = [
            'get users',
            'insert users',
            'update users',
            'delete users',
        ];

        $centerPermissions = [
            'get centers',
            'insert centers',
            'update centers',
            'delete centers',
        ];

        $otherPermissions = [
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
            'delete expedient_person',
        ];

        // 3. Merge all permissions
        $allPermissions = array_merge($userPermissions, $centerPermissions, $otherPermissions);

        // 4. Create permissions (idempotent)
        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // 5. Assign permissions

        // Super admin: all permissions
        $superAdmin->syncPermissions(Permission::all());

        // Admin: all (user + other)
        $admin->syncPermissions(array_merge($userPermissions, $otherPermissions));

        // Visor: only others
        $visor->syncPermissions($otherPermissions);

        // User: only "get" permissions
        $getPermissions = array_filter($otherPermissions, fn($perm) => str_starts_with($perm, 'get '));
        $user->syncPermissions($getPermissions);
    }
}
