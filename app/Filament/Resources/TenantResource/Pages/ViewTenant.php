<?php

namespace App\Filament\Resources\TenantResource\Pages;

use App\Filament\Pages\ProvisionTenant;
use App\Filament\Resources\TenantResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewTenant extends ViewRecord
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('provisionTenant')
                ->label('Provision Another Tenant')
                ->icon('heroicon-o-plus')
                ->url(ProvisionTenant::getUrl()),
        ];
    }
}
