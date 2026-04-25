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
        Schema::create('tenant_domains', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->string('domain', 255)->unique();
            $table->string('kind', 16)->default('subdomain');
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->timestamps();

            $table->index('tenant_id');
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnUpdate()->cascadeOnDelete();
        });

        DB::statement(<<<'SQL'
            ALTER TABLE tenant_domains
            ADD CONSTRAINT tenant_domains_kind_check
            CHECK (kind IN ('subdomain', 'custom'))
        SQL);

        DB::statement(<<<'SQL'
            CREATE UNIQUE INDEX tenant_domains_one_primary_per_tenant
            ON tenant_domains (tenant_id)
            WHERE is_primary = true
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_domains');
    }
};
