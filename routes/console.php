<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Services\Tenancy\TenantProvisioningService;

$defaultTenantDomainHelp = '<slug>.' . config('domains.tenant_base');

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command("tenant:provision
    {slug : Immutable tenant slug such as ourbpo}
    {display_name : Tenant display name}
    {admin_name : Initial tenant admin name}
    {admin_email : Initial tenant admin email}
    {--admin-password= : Initial tenant admin password}
    {--domain= : Primary tenant domain, defaults to {$defaultTenantDomainHelp}}
    {--legal-name= : Legal company name}
    {--timezone=UTC : Tenant timezone}
    {--locale= : Tenant locale such as en-IN}
    {--region=primary : Deployment region marker}
    {--currency=USD : Default tenant currency code}
    {--country= : Default tenant country code}
    {--admin-job-title= : Initial admin job title}
    {--triggered-by-user-id= : Landlord user id that triggered provisioning}", function (TenantProvisioningService $service) {
    $password = $this->option('admin-password') ?: $this->secret('Initial admin password');

    $tenant = $service->provision(
        slug: $this->argument('slug'),
        displayName: $this->argument('display_name'),
        adminName: $this->argument('admin_name'),
        adminEmail: $this->argument('admin_email'),
        adminPassword: $password,
        domain: $this->option('domain') ?: null,
        legalName: $this->option('legal-name') ?: null,
        timezone: $this->option('timezone'),
        locale: $this->option('locale') ?: null,
        region: $this->option('region'),
        currencyCode: $this->option('currency'),
        countryCode: $this->option('country') ?: null,
        adminJobTitle: $this->option('admin-job-title') ?: null,
        triggeredByUserId: $this->option('triggered-by-user-id') ? (int) $this->option('triggered-by-user-id') : null,
    );

    $this->info("Provisioned tenant [{$tenant->slug}] using database [{$tenant->database_name}].");
})->purpose('Provision a tenant end-to-end with database, schema, seed data, organization profile, and initial admin.');
