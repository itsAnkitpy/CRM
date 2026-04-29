<?php

namespace Tests\Feature;

use App\Models\LandlordUser;
use App\Models\Tenant;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Database\Seeders\LandlordRolesSeeder;
use Stancl\Tenancy\Facades\Tenancy;
use Tests\Concerns\BuildsTenantRoutingEnvironment;
use Tests\TestCase;

class TenantEntrySurfaceTest extends TestCase
{
    use BuildsTenantRoutingEnvironment;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootstrapTenantRoutingEnvironment();
        $this->seed(LandlordRolesSeeder::class);
        $this->tenant = $this->createTenant();
    }

    public function test_known_tenant_root_renders_the_shared_landing_page_with_tenant_login_copy(): void
    {
        $this->get('http://acme.crm.test/')
            ->assertOk()
            ->assertSee('Log in to Acme BPO')
            ->assertSee('Open Acme BPO login');
    }

    public function test_known_tenant_login_redirects_into_the_tenant_panel_login(): void
    {
        $this->get('http://acme.crm.test/login')
            ->assertRedirect('http://acme.crm.test/app/login');
    }

    public function test_tenant_panel_login_does_not_use_tenant_asset_proxy_urls(): void
    {
        $this->get('http://acme.crm.test/app/login')
            ->assertOk()
            ->assertDontSee('/tenancy/assets/', escape: false);
    }

    public function test_livewire_update_route_uses_tenancy_aware_middleware(): void
    {
        $route = app('router')->getRoutes()->getByName('livewire.update');

        $this->assertNotNull($route);
        $this->assertNotSame('default-livewire.update', $route->getName());
        $this->assertArrayHasKey('universal', app('router')->getMiddlewareGroups());
        $this->assertContains('universal', $route->gatherMiddleware());
        $this->assertContains(\Stancl\Tenancy\Middleware\InitializeTenancyByDomain::class, $route->gatherMiddleware());
    }

    public function test_livewire_update_endpoint_resolves_without_container_errors_on_landlord_and_tenant_hosts(): void
    {
        $updateUri = app('livewire')->getUpdateUri();
        $headers = ['X-Livewire' => '1'];
        $payload = ['components' => []];

        $this->withHeaders($headers)
            ->postJson("http://hcore.crm.test{$updateUri}", $payload)
            ->assertNotFound();

        $this->withHeaders($headers)
            ->postJson("http://acme.crm.test{$updateUri}", $payload)
            ->assertNotFound();
    }

    public function test_authenticated_tenant_user_is_redirected_from_public_root_to_the_tenant_panel(): void
    {
        $user = $this->createTenantUser($this->tenant, [
            'email' => 'agent@acme.test',
        ]);

        $this->actingAs($user, 'tenant');

        $this->get('http://acme.crm.test/')
            ->assertRedirect('http://acme.crm.test/app');
    }

    public function test_authenticated_tenant_user_is_redirected_from_public_login_to_the_tenant_panel(): void
    {
        $user = $this->createTenantUser($this->tenant, [
            'email' => 'manager@acme.test',
        ]);

        $this->actingAs($user, 'tenant');

        $this->get('http://acme.crm.test/login')
            ->assertRedirect('http://acme.crm.test/app');
    }

    public function test_central_hosts_cannot_access_tenant_panel_routes(): void
    {
        $this->get('http://crm.test/app/login')
            ->assertNotFound();
    }

    public function test_unknown_tenant_hosts_return_not_found_for_public_and_panel_routes(): void
    {
        $this->get('http://ghost.crm.test/')
            ->assertNotFound();

        $this->get('http://ghost.crm.test/login')
            ->assertNotFound();

        $this->get('http://ghost.crm.test/app/login')
            ->assertNotFound();
    }

    public function test_landlord_and_tenant_hosts_use_different_session_cookie_names(): void
    {
        $landlordResponse = $this->get('http://hcore.crm.test/login');
        $tenantResponse = $this->get('http://acme.crm.test/app/login');

        $this->assertStringContainsString('crm_landlord_session=', implode("\n", $landlordResponse->headers->getCookies()));
        $this->assertStringContainsString('crm_tenant_session=', implode("\n", $tenantResponse->headers->getCookies()));
    }

    public function test_landlord_authentication_does_not_unlock_tenant_panel_routes(): void
    {
        $landlordUser = LandlordUser::factory()->create();
        $landlordUser->assignRole('super_admin');

        $this->actingAs($landlordUser, 'landlord');

        $this->get('http://acme.crm.test/app')
            ->assertRedirect('http://acme.crm.test/app/login');
    }

    public function test_tenant_authentication_does_not_unlock_landlord_routes(): void
    {
        $tenantUser = $this->createTenantUser($this->tenant, [
            'email' => 'owner@acme.test',
        ]);

        $this->actingAs($tenantUser, 'tenant');

        $this->get('http://hcore.crm.test/')
            ->assertRedirect('http://hcore.crm.test/login');
    }

    public function test_tenant_guard_authenticates_against_the_current_tenant_database(): void
    {
        $tenantUser = $this->createTenantUser($this->tenant, [
            'email' => 'owner@acme.test',
            'password' => 'secret-pass-123',
        ]);

        Filament::setCurrentPanel(Filament::getPanel('tenant'));
        Tenancy::initialize($this->tenant);

        try {
            $guard = app('auth')->guard('tenant');
            $provider = $guard->getProvider();
            $credentials = [
                'email' => 'owner@acme.test',
                'password' => 'secret-pass-123',
            ];

            $resolvedUser = $provider->retrieveByCredentials($credentials);

            $this->assertNotNull($resolvedUser);
            $this->assertSame($tenantUser->email, $resolvedUser->email);
            $this->assertTrue($provider->validateCredentials($resolvedUser, $credentials));
            $this->assertTrue($guard->attemptWhen(
                $credentials,
                fn ($user): bool => ! ($user instanceof FilamentUser) || $user->canAccessPanel(Filament::getPanel('tenant')),
            ));
            $this->assertAuthenticated('tenant');
        } finally {
            $guard->logout();
            Tenancy::end();
        }
    }
}
