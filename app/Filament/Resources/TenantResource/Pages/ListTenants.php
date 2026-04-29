<?php

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Pages\ProvisionTenant;
use App\Filament\Resources\TenantResource;
use App\Models\LandlordUser;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListTenants extends ListRecords
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('provisionTenant')
                ->label('Provision Tenant')
                ->icon('heroicon-o-plus')
                ->visible(fn (): bool => auth()->user() instanceof LandlordUser && auth()->user()->canProvisionTenants())
                ->url(ProvisionTenant::getUrl()),
        ];
    }
}
