<?php

namespace App\Services\Tenancy;

use App\Exceptions\TenantProvisioningException;
use App\Models\LandlordUser;
use App\Models\Tenant;
use App\Models\TenantProvisioningRun;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Spatie\Permission\Models\Role;
use Stancl\Tenancy\Facades\Tenancy;
use Stancl\Tenancy\Jobs\CreateDatabase;
use Stancl\Tenancy\Jobs\MigrateDatabase;
use Stancl\Tenancy\Jobs\SeedDatabase;
use Throwable;

class TenantProvisioningService
{
    protected const RESERVED_SLUGS = [
        'admin',
        'api',
        'app',
        'www',
        'landlord',
        'support',
        'status',
        'hcore',
    ];

    public function provision(
        string $slug,
        string $displayName,
        string $adminName,
        string $adminEmail,
        string $adminPassword,
        ?string $domain = null,
        ?string $legalName = null,
        string $timezone = 'UTC',
        ?string $locale = null,
        string $region = 'primary',
        string $currencyCode = 'USD',
        ?string $countryCode = null,
        ?string $adminJobTitle = null,
        ?int $triggeredByUserId = null,
    ): Tenant {
        $this->validateInput(
            slug: $slug,
            displayName: $displayName,
            adminName: $adminName,
            adminEmail: $adminEmail,
            adminPassword: $adminPassword,
            domain: $domain,
            timezone: $timezone,
            locale: $locale,
            region: $region,
            currencyCode: $currencyCode,
            countryCode: $countryCode,
            triggeredByUserId: $triggeredByUserId,
        );

        $slug = Str::lower($slug);
        $domain ??= $this->defaultDomainForSlug($slug);
        $databaseName = $this->databaseNameFromSlug($slug);
        $run = null;

        /** @var Tenant $tenant */
        $tenant = DB::connection('landlord')->transaction(function () use (
            $slug,
            $displayName,
            $legalName,
            $timezone,
            $locale,
            $region,
            $databaseName,
            $domain,
            &$run,
            $triggeredByUserId,
        ) {
            $tenant = Tenant::withoutEvents(function () use (
                $slug,
                $displayName,
                $legalName,
                $timezone,
                $locale,
                $region,
                $databaseName,
            ) {
                return Tenant::query()->create([
                    'id' => (string) Str::uuid(),
                    'slug' => $slug,
                    'display_name' => $displayName,
                    'legal_name' => $legalName,
                    'state' => 'provisioning',
                    'database_name' => $databaseName,
                    'region' => $region,
                    'timezone' => $timezone,
                    'locale' => $locale,
                ]);
            });

            $tenant->createDomain([
                'domain' => $domain,
                'kind' => $domain === $this->defaultDomainForSlug($slug) ? 'subdomain' : 'custom',
                'is_primary' => true,
                'is_verified' => true,
            ]);

            $run = TenantProvisioningRun::query()->create([
                'tenant_id' => $tenant->id,
                'status' => 'running',
                'current_step' => 'database',
                'triggered_by_user_id' => $triggeredByUserId,
                'started_at' => now(),
            ]);

            return $tenant->fresh();
        });

        try {
            $this->runStanclJob(new CreateDatabase($tenant), $run, 'database');
            $this->runStanclJob(new MigrateDatabase($tenant), $run, 'migrations');
            $this->runStanclJob(new SeedDatabase($tenant), $run, 'seed');

            Tenancy::initialize($tenant);

            try {
                $this->updateRun($run, 'organization_profile');
                $this->createOrganizationProfile(
                    displayName: $displayName,
                    legalName: $legalName,
                    timezone: $timezone,
                    locale: $locale,
                    currencyCode: $currencyCode,
                    countryCode: $countryCode,
                    primaryEmail: $adminEmail,
                );

                $this->updateRun($run, 'initial_admin');
                $this->createInitialAdmin(
                    name: $adminName,
                    email: $adminEmail,
                    password: $adminPassword,
                    timezone: $timezone,
                    jobTitle: $adminJobTitle,
                );
            } finally {
                Tenancy::end();
            }

            $this->updateRun($run, 'activation');

            $tenant->forceFill([
                'state' => 'active',
                'activated_at' => now(),
            ])->save();

            $run->update([
                'status' => 'completed',
                'current_step' => 'completed',
                'finished_at' => now(),
            ]);

            return $tenant->fresh();
        } catch (Throwable $exception) {
            if ($run) {
                $run->update([
                    'status' => 'failed',
                    'error_message' => Str::limit($exception->getMessage(), 65535, ''),
                    'finished_at' => now(),
                ]);
            }

            throw $run ? TenantProvisioningException::fromRun($run->fresh(), $exception) : $exception;
        }
    }

    protected function runStanclJob(object $job, TenantProvisioningRun $run, string $step): void
    {
        $this->updateRun($run, $step);

        app()->call([$job, 'handle']);
    }

    protected function updateRun(TenantProvisioningRun $run, string $step): void
    {
        $run->forceFill([
            'current_step' => $step,
        ])->save();
    }

    protected function createOrganizationProfile(
        string $displayName,
        ?string $legalName,
        string $timezone,
        ?string $locale,
        string $currencyCode,
        ?string $countryCode,
        string $primaryEmail,
    ): void {
        DB::table('organization_profile')->updateOrInsert(
            ['id' => 1],
            [
                'public_id' => (string) Str::uuid(),
                'display_name' => $displayName,
                'legal_name' => $legalName,
                'timezone' => $timezone,
                'locale' => $locale,
                'currency_code' => Str::upper($currencyCode),
                'country_code' => $countryCode ? Str::upper($countryCode) : null,
                'primary_email' => $primaryEmail,
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    protected function createInitialAdmin(
        string $name,
        string $email,
        string $password,
        string $timezone,
        ?string $jobTitle,
    ): void {
        /** @var User $user */
        $user = User::query()->create([
            'public_id' => (string) Str::uuid(),
            'status' => 'active',
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'job_title' => $jobTitle,
            'timezone' => $timezone,
        ]);

        $role = Role::query()->where('name', 'tenant_admin')->where('guard_name', 'web')->first();

        if (! $role) {
            throw new InvalidArgumentException('Baseline tenant roles are missing. The tenant seed step did not create tenant_admin.');
        }

        $user->assignRole($role);
    }

    protected function validateInput(
        string $slug,
        string $displayName,
        string $adminName,
        string $adminEmail,
        string $adminPassword,
        ?string $domain,
        string $timezone,
        ?string $locale,
        string $region,
        string $currencyCode,
        ?string $countryCode,
        ?int $triggeredByUserId,
    ): void {
        Validator::make([
            'slug' => $slug,
            'display_name' => $displayName,
            'admin_name' => $adminName,
            'admin_email' => $adminEmail,
            'admin_password' => $adminPassword,
            'domain' => $domain,
            'timezone' => $timezone,
            'locale' => $locale,
            'region' => $region,
            'currency_code' => $currencyCode,
            'country_code' => $countryCode,
            'triggered_by_user_id' => $triggeredByUserId,
        ], [
            'slug' => ['required', 'string', 'max:40', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'display_name' => ['required', 'string', 'max:255'],
            'admin_name' => ['required', 'string', 'max:255'],
            'admin_email' => ['required', 'email', 'max:255'],
            'admin_password' => ['required', 'string', 'min:8'],
            'domain' => ['nullable', 'string', 'max:255'],
            'timezone' => ['required', 'timezone'],
            'locale' => ['nullable', 'string', 'max:35'],
            'region' => ['required', 'string', 'max:32'],
            'currency_code' => ['required', 'string', 'size:3'],
            'country_code' => ['nullable', 'string', 'size:2'],
            'triggered_by_user_id' => ['nullable', 'integer', 'exists:landlord.users,id'],
        ])->validate();

        if (in_array($slug, self::RESERVED_SLUGS, true)) {
            throw new InvalidArgumentException("The slug [{$slug}] is reserved.");
        }

        if (Tenant::query()->where('slug', $slug)->exists()) {
            throw new InvalidArgumentException("A tenant with slug [{$slug}] already exists.");
        }

        if ($domain && \App\Models\TenantDomain::query()->where('domain', $domain)->exists()) {
            throw new InvalidArgumentException("The domain [{$domain}] is already in use.");
        }

        if ($triggeredByUserId && ! LandlordUser::query()->whereKey($triggeredByUserId)->exists()) {
            throw new InvalidArgumentException("The triggering landlord user [{$triggeredByUserId}] does not exist.");
        }
    }

    protected function databaseNameFromSlug(string $slug): string
    {
        return 'crm_tenant_' . Str::of($slug)->replace('-', '_');
    }

    protected function defaultDomainForSlug(string $slug): string
    {
        return Str::lower($slug) . '.' . config('domains.tenant_base');
    }
}
