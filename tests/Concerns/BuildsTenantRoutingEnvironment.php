<?php

namespace Tests\Concerns;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Stancl\Tenancy\Facades\Tenancy;

trait BuildsTenantRoutingEnvironment
{
    use BuildsLandlordAuthorizationSchema;

    /** @var array<int, string> */
    protected array $tenantDatabasePaths = [];

    protected function bootstrapTenantRoutingEnvironment(): void
    {
        $this->bootstrapLandlordAuthorizationSchema();

        config()->set('database.connections.tenant_template', [
            'driver' => 'sqlite',
            'database' => null,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        config()->set('session.driver', 'file');
        config()->set('session.files', storage_path('framework/sessions'));

        DB::purge('tenant_template');

        $this->beforeApplicationDestroyed(function (): void {
            foreach ($this->tenantDatabasePaths as $path) {
                if (is_file($path)) {
                    @unlink($path);
                }
            }
        });
    }

    protected function createTenant(string $slug = 'acme', string $displayName = 'Acme BPO', ?string $domain = null): Tenant
    {
        $relativeDatabasePath = 'tmp/' . Str::random(16) . '.sqlite';
        $databasePath = database_path($relativeDatabasePath);

        if (! is_dir(dirname($databasePath))) {
            mkdir(dirname($databasePath), 0777, true);
        }

        touch($databasePath);

        $this->tenantDatabasePaths[] = $databasePath;

        /** @var Tenant $tenant */
        $tenant = Tenant::query()->create([
            'id' => (string) Str::uuid(),
            'slug' => $slug,
            'display_name' => $displayName,
            'state' => 'active',
            'database_name' => $relativeDatabasePath,
            'region' => 'primary',
            'timezone' => 'Asia/Kolkata',
            'locale' => 'en-IN',
        ]);

        $tenant->createDomain([
            'domain' => $domain ?? "{$slug}." . config('domains.tenant_base'),
            'kind' => 'subdomain',
            'is_primary' => true,
            'is_verified' => true,
        ]);

        $connection = 'tenant_setup_' . Str::lower(Str::random(8));

        config()->set("database.connections.{$connection}", [
            'driver' => 'sqlite',
            'database' => $databasePath,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        DB::purge($connection);

        Schema::connection($connection)->create('users', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->nullable()->unique();
            $table->string('status', 32)->default('active');
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

        return $tenant->fresh();
    }

    protected function createTenantUser(Tenant $tenant, array $attributes = []): User
    {
        Tenancy::initialize($tenant);

        try {
            /** @var User $user */
            $user = User::factory()->create(array_merge([
                'status' => 'active',
                'timezone' => 'Asia/Kolkata',
            ], $attributes));
        } finally {
            Tenancy::end();
        }

        return $user;
    }
}
