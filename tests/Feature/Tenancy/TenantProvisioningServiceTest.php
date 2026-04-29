<?php

namespace Tests\Feature\Tenancy;

use App\Models\LandlordUser;
use App\Models\User;
use App\Services\Tenancy\TenantProvisioningService;
use Database\Seeders\TenantRolesSeeder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Tests\Concerns\BuildsLandlordAuthorizationSchema;
use Tests\TestCase;

class TenantProvisioningServiceTest extends TestCase
{
    use BuildsLandlordAuthorizationSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->bootstrapLandlordAuthorizationSchema();
        $this->seed(TenantRolesSeeder::class);
    }

    public function test_initial_tenant_admin_is_created_and_assigned_using_the_tenant_user_model(): void
    {
        $service = new class extends TenantProvisioningService
        {
            public function createInitialAdminForTest(
                string $name,
                string $email,
                string $password,
                string $timezone,
                ?string $jobTitle,
            ): void {
                $this->createInitialAdmin($name, $email, $password, $timezone, $jobTitle);
            }
        };

        $service->createInitialAdminForTest(
            name: 'Amit Sharma',
            email: 'admin@acme.test',
            password: 'secret-pass-123',
            timezone: 'Asia/Kolkata',
            jobTitle: 'Operations Manager',
        );

        $user = User::query()->where('email', 'admin@acme.test')->first();
        $tenantAdminRoleId = DB::table('roles')->where('name', 'tenant_admin')->value('id');

        $this->assertNotNull($user);
        $this->assertNotNull($tenantAdminRoleId);
        $this->assertSame('active', $user->status);
        $this->assertSame('Operations Manager', $user->job_title);
        $this->assertSame('Asia/Kolkata', $user->timezone);

        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => $tenantAdminRoleId,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);

        $this->assertDatabaseMissing('model_has_roles', [
            'model_type' => LandlordUser::class,
            'model_id' => $user->id,
        ]);
    }

    public function test_reserved_hcore_slug_is_rejected_before_provisioning_starts(): void
    {
        $service = new class extends TenantProvisioningService
        {
            public function validateForTest(string $slug): void
            {
                $this->validateInput(
                    slug: $slug,
                    displayName: 'Highland Core',
                    adminName: 'Amit Sharma',
                    adminEmail: 'admin@highland.test',
                    adminPassword: 'secret-pass-123',
                    domain: null,
                    timezone: 'Asia/Kolkata',
                    locale: 'en-IN',
                    region: 'primary',
                    currencyCode: 'INR',
                    countryCode: 'IN',
                    triggeredByUserId: null,
                );
            }
        };

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The slug [hcore] is reserved.');

        $service->validateForTest('hcore');
    }
}
