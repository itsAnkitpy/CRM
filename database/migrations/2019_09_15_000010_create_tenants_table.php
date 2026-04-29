<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('slug', 40)->unique();
            $table->string('display_name');
            $table->string('legal_name')->nullable();
            $table->string('state', 32)->default('provisioning');
            $table->string('database_name', 63)->unique();
            $table->string('region', 32)->default('primary');
            $table->string('timezone', 64)->default('UTC');
            $table->string('locale', 35)->nullable();
            $table->timestamp('activated_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->jsonb('settings')->nullable();
            $table->timestamps();
        });

        DB::statement(<<<'SQL'
            ALTER TABLE tenants
            ADD CONSTRAINT tenants_state_check
            CHECK (state IN ('provisioning', 'active', 'suspended', 'archived'))
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE tenants
            ADD CONSTRAINT tenants_slug_format_check
            CHECK (slug ~ '^[a-z0-9]+(?:-[a-z0-9]+)*$')
        SQL);

        DB::statement(<<<'SQL'
            ALTER TABLE tenants
            ADD CONSTRAINT tenants_slug_reserved_check
            CHECK (slug NOT IN ('admin', 'api', 'app', 'www', 'landlord', 'support', 'status', 'hcore'))
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
