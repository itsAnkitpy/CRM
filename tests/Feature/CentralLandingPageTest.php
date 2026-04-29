<?php

namespace Tests\Feature;

use Filament\Facades\Filament;
use Tests\TestCase;

class CentralLandingPageTest extends TestCase
{
    public function test_marketing_host_root_shows_the_marketing_landing_page(): void
    {
        $this->get('http://crm.test/')
            ->assertOk()
            ->assertSee('Highland Core')
            ->assertSee('Log in to Highland Core');
    }

    public function test_marketing_host_login_redirects_to_the_landlord_login_host(): void
    {
        $this->get('http://crm.test/login')
            ->assertRedirect(Filament::getPanel('admin')->getLoginUrl());
    }

    public function test_landlord_login_renders_on_the_dedicated_landlord_host(): void
    {
        $this->get('http://hcore.crm.test/login')
            ->assertOk()
            ->assertSee('email', escape: false);
    }

    public function test_landlord_root_does_not_render_the_marketing_landing_page(): void
    {
        $this->get('http://hcore.crm.test/')
            ->assertRedirect('http://hcore.crm.test/login');
    }
}
