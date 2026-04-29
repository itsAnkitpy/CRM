<?php

namespace Tests\Feature\Filament;

use App\Exceptions\TenantProvisioningException;
use App\Filament\Pages\ProvisionTenant;
use App\Models\LandlordUser;
use App\Models\Tenant;
use App\Models\TenantProvisioningRun;
use App\Services\Tenancy\TenantProvisioningService;
use Database\Seeders\LandlordRolesSeeder;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Event;
use InvalidArgumentException;
use Livewire\Livewire;
use Mockery;
use Tests\Concerns\BuildsLandlordAuthorizationSchema;
use Tests\TestCase;

class ProvisionTenantPageTest extends TestCase
{
    use BuildsLandlordAuthorizationSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootstrapLandlordAuthorizationSchema();
        $this->seed(LandlordRolesSeeder::class);

        Event::fake();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_super_admin_can_open_the_tenant_list_page(): void
    {
        $this->actingAs($this->makeLandlordUser('super_admin'));

        $this->get('http://hcore.crm.test/tenants')
            ->assertOk()
            ->assertSee('Tenants');
    }

    public function test_platform_operator_can_open_the_tenant_list_page(): void
    {
        $this->actingAs($this->makeLandlordUser('platform_operator'));

        $this->get('http://hcore.crm.test/tenants')
            ->assertOk()
            ->assertSee('Tenants');
    }

    public function test_platform_operator_can_view_the_provision_tenant_page(): void
    {
        $this->actingAs($this->makeLandlordUser('platform_operator'));

        $this->get('http://hcore.crm.test/tenants/provision')
            ->assertOk()
            ->assertSee('Provision Tenant')
            ->assertSee('Tenant Details')
            ->assertSee('Initial Admin');
    }

    public function test_landlord_user_without_permissions_cannot_open_the_tenant_list_page(): void
    {
        $this->actingAs($this->makeLandlordUser());

        $this->get('http://hcore.crm.test/tenants')
            ->assertForbidden();
    }

    public function test_landlord_user_without_permissions_cannot_open_the_provision_tenant_page(): void
    {
        $this->actingAs($this->makeLandlordUser());

        $this->get('http://hcore.crm.test/tenants/provision')
            ->assertForbidden();
    }

    public function test_page_shows_success_notification_when_tenant_is_provisioned(): void
    {
        $user = $this->makeLandlordUser('platform_operator');
        $this->actingAs($user);

        $tenant = $this->makeTenantResult(
            displayName: 'Acme BPO',
            databaseName: 'crm_tenant_acme',
            primaryDomain: 'acme.crm.test',
        );

        $service = Mockery::mock(TenantProvisioningService::class);
        $service->shouldReceive('provision')
            ->once()
            ->withArgs(function (
                string $slug,
                string $displayName,
                string $adminName,
                string $adminEmail,
                string $adminPassword,
                ?string $domain,
                ?string $legalName,
                string $timezone,
                ?string $locale,
                string $region,
                string $currencyCode,
                ?string $countryCode,
                ?string $adminJobTitle,
                ?int $triggeredByUserId,
            ) use ($user): bool {
                return $slug === 'acme'
                    && $displayName === 'Acme BPO'
                    && $adminName === 'Amit Sharma'
                    && $adminEmail === 'admin@acme.test'
                    && $adminPassword === 'secret-pass-123'
                    && $domain === null
                    && $legalName === null
                    && $timezone === 'Asia/Kolkata'
                    && $locale === 'en-IN'
                    && $region === 'primary'
                    && $currencyCode === 'INR'
                    && $countryCode === 'IN'
                    && $adminJobTitle === 'Operations Manager'
                    && $triggeredByUserId === $user->id;
            })
            ->andReturn($tenant);

        $this->app->instance(TenantProvisioningService::class, $service);

        Livewire::test(ProvisionTenant::class)
            ->fillForm([
                'slug' => 'acme',
                'display_name' => 'Acme BPO',
                'admin_name' => 'Amit Sharma',
                'admin_email' => 'admin@acme.test',
                'admin_password' => 'secret-pass-123',
                'admin_job_title' => 'Operations Manager',
            ])
            ->call('provision')
            ->assertHasNoFormErrors()
            ->assertNotified(
                Notification::make()
                    ->success()
                    ->title('Tenant provisioned successfully')
                    ->body('Acme BPO is now active at acme.crm.test using database crm_tenant_acme.')
            )
            ->assertFormSet([
                'timezone' => 'Asia/Kolkata',
                'locale' => 'en-IN',
                'region' => 'primary',
                'currency_code' => 'INR',
                'country_code' => 'IN',
            ]);
    }

    public function test_page_shows_validation_style_error_when_provisioning_is_rejected_before_start(): void
    {
        $this->actingAs($this->makeLandlordUser('platform_operator'));

        $service = Mockery::mock(TenantProvisioningService::class);
        $service->shouldReceive('provision')
            ->once()
            ->andThrow(new InvalidArgumentException('A tenant with slug [acme] already exists.'));

        $this->app->instance(TenantProvisioningService::class, $service);

        Livewire::test(ProvisionTenant::class)
            ->fillForm([
                'slug' => 'acme',
                'display_name' => 'Acme BPO',
                'admin_name' => 'Amit Sharma',
                'admin_email' => 'admin@acme.test',
                'admin_password' => 'secret-pass-123',
            ])
            ->call('provision')
            ->assertNotified(
                Notification::make()
                    ->danger()
                    ->title('Could not provision tenant')
                    ->body('A tenant with slug [acme] already exists.')
            );
    }

    public function test_page_shows_provisioning_failure_message_when_background_steps_fail(): void
    {
        $this->actingAs($this->makeLandlordUser('platform_operator'));

        $run = new TenantProvisioningRun([
            'current_step' => 'migrations',
        ]);

        $service = Mockery::mock(TenantProvisioningService::class);
        $service->shouldReceive('provision')
            ->once()
            ->andThrow(new TenantProvisioningException(
                run: $run,
                message: 'Provisioning failed during migrations. The tenant remains in provisioning. Database unavailable.',
            ));

        $this->app->instance(TenantProvisioningService::class, $service);

        Livewire::test(ProvisionTenant::class)
            ->fillForm([
                'slug' => 'acme',
                'display_name' => 'Acme BPO',
                'admin_name' => 'Amit Sharma',
                'admin_email' => 'admin@acme.test',
                'admin_password' => 'secret-pass-123',
            ])
            ->call('provision')
            ->assertNotified(
                Notification::make()
                    ->danger()
                    ->title('Tenant provisioning failed')
                    ->body('Provisioning failed during migrations. The tenant remains in provisioning. Database unavailable.')
                    ->persistent()
            );
    }

    protected function makeLandlordUser(?string $role = null): LandlordUser
    {
        $user = LandlordUser::factory()->create();

        if ($role) {
            $user->assignRole($role);
        }

        return $user;
    }

    protected function makeTenantResult(string $displayName, string $databaseName, string $primaryDomain): Tenant
    {
        $domains = Mockery::mock();
        $domains->shouldReceive('where')
            ->once()
            ->with('is_primary', true)
            ->andReturnSelf();
        $domains->shouldReceive('value')
            ->once()
            ->with('domain')
            ->andReturn($primaryDomain);

        /** @var Tenant&\Mockery\MockInterface $tenant */
        $tenant = Mockery::mock(Tenant::class)->makePartial();
        $tenant->display_name = $displayName;
        $tenant->database_name = $databaseName;
        $tenant->shouldReceive('domains')
            ->once()
            ->andReturn($domains);

        return $tenant;
    }
}
