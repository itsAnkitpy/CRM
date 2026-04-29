<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE tenants DROP CONSTRAINT IF EXISTS tenants_slug_reserved_check');

        DB::statement(<<<'SQL'
            ALTER TABLE tenants
            ADD CONSTRAINT tenants_slug_reserved_check
            CHECK (slug NOT IN ('admin', 'api', 'app', 'www', 'landlord', 'support', 'status', 'hcore'))
        SQL);
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE tenants DROP CONSTRAINT IF EXISTS tenants_slug_reserved_check');

        DB::statement(<<<'SQL'
            ALTER TABLE tenants
            ADD CONSTRAINT tenants_slug_reserved_check
            CHECK (slug NOT IN ('admin', 'api', 'app', 'www', 'landlord', 'support', 'status'))
        SQL);
    }
};
