<?php

use App\Http\Controllers\LandingPageController;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Route;

foreach (config('domains.marketing_hosts', ['localhost']) as $domain) {
    Route::domain($domain)->group(function () {
        Route::get('/', [LandingPageController::class, 'central'])
            ->name('landing.central');

        Route::get('/login', function () {
            return redirect()->to(Filament::getPanel('admin')->getLoginUrl());
        })->name('landing.central.login');
    });
}
