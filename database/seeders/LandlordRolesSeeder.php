<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class LandlordRolesSeeder extends Seeder
{
    /**
     * Seed baseline landlord roles and permissions.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'landlord.tenants.view',
            'landlord.tenants.provision',
        ];

        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        $platformOperator = Role::findOrCreate('platform_operator', 'web');
        Role::findOrCreate('super_admin', 'web');

        $platformOperator->syncPermissions($permissions);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
