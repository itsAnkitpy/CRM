<?php

namespace App\Providers;

use App\Models\LandlordUser;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function ($user, string $ability): ?bool {
            if (! $user instanceof LandlordUser) {
                return null;
            }

            if (! str_starts_with($ability, 'landlord.')) {
                return null;
            }

            return $user->hasRole('super_admin') ? true : null;
        });
    }
}
