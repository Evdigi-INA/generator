<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\{Role, Permission};

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roleAdmin = Role::create(['name' => 'admin']);

        foreach (config('permission.permissions') as $permission) {
            foreach ($permission['access'] as $access) {
                Permission::create(['name' => $access]);
            }
        }

        $userAdmin = User::first();
        $userAdmin->assignRole('admin');
        $roleAdmin->givePermissionTo(Permission::all());
    }
}
