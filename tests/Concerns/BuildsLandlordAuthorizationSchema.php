<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\PermissionRegistrar;

trait BuildsLandlordAuthorizationSchema
{
    protected function bootstrapLandlordAuthorizationSchema(): void
    {
        config()->set('database.connections.landlord', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        config()->set('database.default', 'landlord');

        DB::purge('landlord');
        app('db')->setDefaultConnection('landlord');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->nullable()->unique();
            $table->string('status', 32)->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('job_title')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_e164', 20)->nullable();
            $table->string('timezone', 64)->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name');
            $table->timestamps();

            $table->unique(['name', 'guard_name']);
        });

        Schema::create('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type'], 'model_has_permissions_model_id_model_type_index');
            $table->primary(
                ['permission_id', 'model_id', 'model_type'],
                'model_has_permissions_permission_model_type_primary'
            );
        });

        Schema::create('model_has_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('role_id');
            $table->string('model_type');
            $table->unsignedBigInteger('model_id');
            $table->index(['model_id', 'model_type'], 'model_has_roles_model_id_model_type_index');
            $table->primary(
                ['role_id', 'model_id', 'model_type'],
                'model_has_roles_role_model_type_primary'
            );
        });

        Schema::create('role_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('permission_id');
            $table->unsignedBigInteger('role_id');

            $table->primary(
                ['permission_id', 'role_id'],
                'role_has_permissions_permission_id_role_id_primary'
            );
        });

        Schema::create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();
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
            $table->text('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('tenant_domains', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('tenant_id');
            $table->string('domain', 255)->unique();
            $table->string('kind', 16)->default('subdomain');
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_verified')->default(false);
            $table->timestamps();
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
