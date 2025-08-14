<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roleAdmin = Role::create(attributes: ['name' => 'admin']);

        foreach (config(key: 'permission.permissions') as $permission) {
            foreach ($permission['access'] as $access) {
                Permission::create(attributes: ['name' => $access]);
            }
        }

        $userAdmin = User::first();
        $userAdmin->assignRole(roles: 'admin');
        $roleAdmin->givePermissionTo(permissions: Permission::all());
    }
}
