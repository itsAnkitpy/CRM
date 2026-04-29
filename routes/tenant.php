<?php

declare(strict_types=1);

use App\Http\Controllers\LandingPageController;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenantRouteServiceProvider.
|
| Feel free to customize them however you want. Good luck!
|
*/

Route::middleware([
    'web',
    InitializeTenancyByDomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/', [LandingPageController::class, 'tenant'])
        ->name('landing.tenant');

    Route::get('/login', function () {
        if (auth()->guard('tenant')->check()) {
            return redirect()->to(Filament::getPanel('tenant')->getUrl());
        }

        return redirect()->to(Filament::getPanel('tenant')->getLoginUrl());
    })->name('landing.tenant.login');
});
