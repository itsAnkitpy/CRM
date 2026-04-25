<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class TenantRolesSeeder extends Seeder
{
    /**
     * Seed baseline tenant roles.
     */
    public function run(): void
    {
        foreach (['tenant_admin', 'manager', 'agent', 'qa'] as $roleName) {
            Role::query()->firstOrCreate([
                'name' => $roleName,
                'guard_name' => 'web',
            ]);
        }
    }
}
