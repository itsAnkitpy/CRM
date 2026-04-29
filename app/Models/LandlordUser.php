<?php

namespace App\Models;

use Database\Factories\LandlordUserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Stancl\Tenancy\Database\Concerns\CentralConnection;
use Spatie\Permission\Traits\HasRoles;

class LandlordUser extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<LandlordUserFactory> */
    use CentralConnection, HasFactory, HasRoles, Notifiable;

    protected string $guard_name = 'web';

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() === 'admin' && $this->hasLandlordAccess();
    }

    public function hasLandlordAccess(): bool
    {
        return $this->canViewTenants() || $this->canProvisionTenants();
    }

    public function canViewTenants(): bool
    {
        return $this->can('landlord.tenants.view');
    }

    public function canProvisionTenants(): bool
    {
        return $this->can('landlord.tenants.provision');
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    protected static function newFactory(): LandlordUserFactory
    {
        return LandlordUserFactory::new();
    }
}
