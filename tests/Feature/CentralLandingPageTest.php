<?php

namespace Tests\Feature;

use Tests\TestCase;

class CentralLandingPageTest extends TestCase
{
    public function test_central_domain_root_shows_the_marketing_landing_page(): void
    {
        $this->get('http://localhost/')
            ->assertOk()
            ->assertSee('Highland Core')
            ->assertSee('Log in to Highland Core');
    }

    public function test_central_login_route_redirects_to_filament_admin_login(): void
    {
        $this->get('http://localhost/login')
            ->assertRedirect(route('filament.admin.auth.login'));
    }
}
