<?php

use Illuminate\Support\Facades\Route;

foreach (config('tenancy.central_domains', ['localhost']) as $domain) {
    Route::domain($domain)->group(function () {
        Route::view('/', 'welcome');

        Route::get('/login', function () {
            return redirect()->route('filament.admin.auth.login');
        });
    });
}
