<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantDatabaseSeeder extends Seeder
{
    /**
     * Seed the tenant database.
     */
    public function run(): void
    {
        $this->call([
            TenantRolesSeeder::class,
            TenantWorkflowSeeder::class,
        ]);
    }
}
