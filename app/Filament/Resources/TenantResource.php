<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantResource\Pages;
use App\Filament\Resources\TenantResource\RelationManagers\DomainsRelationManager;
use App\Filament\Resources\TenantResource\RelationManagers\ProvisioningRunsRelationManager;
use App\Models\LandlordUser;
use App\Models\Tenant;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TenantResource extends Resource
{
    protected static ?string $model = Tenant::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string | \UnitEnum | null $navigationGroup = 'Platform';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'display_name';

    public static function getNavigationLabel(): string
    {
        return 'Tenants';
    }

    public static function getModelLabel(): string
    {
        return 'Tenant';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Tenants';
    }

    public static function canViewAny(): bool
    {
        return static::landlordUser()?->canViewTenants() ?? false;
    }

    public static function canView(Model $record): bool
    {
        return static::landlordUser()?->canViewTenants() ?? false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'primaryDomain',
                'latestProvisioningRun.triggeredByUser',
            ])
            ->latest('created_at');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('display_name')
                    ->label('Tenant')
                    ->description(fn (Tenant $record): string => $record->slug)
                    ->searchable(['display_name', 'slug'])
                    ->sortable(),
                TextColumn::make('primaryDomain.domain')
                    ->label('Primary Domain')
                    ->placeholder('No domain recorded')
                    ->copyable(),
                TextColumn::make('database_name')
                    ->label('Database')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('state')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'provisioning' => 'warning',
                        'suspended' => 'danger',
                        'archived' => 'gray',
                        default => 'gray',
                    }),
                TextColumn::make('latestProvisioningRun.status')
                    ->label('Latest Run')
                    ->badge()
                    ->placeholder('No runs yet')
                    ->color(fn (?string $state): string => match ($state) {
                        'completed' => 'success',
                        'running' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('latestProvisioningRun.current_step')
                    ->label('Current Step')
                    ->placeholder('Completed')
                    ->toggleable(),
                TextColumn::make('activated_at')
                    ->label('Activated')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('state')
                    ->options([
                        'provisioning' => 'Provisioning',
                        'active' => 'Active',
                        'suspended' => 'Suspended',
                        'archived' => 'Archived',
                    ]),
                SelectFilter::make('latest_run_status')
                    ->label('Latest Run Status')
                    ->options([
                        'running' => 'Running',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $status = $data['value'] ?? null;

                        if (! filled($status)) {
                            return $query;
                        }

                        return $query->whereHas('latestProvisioningRun', fn (Builder $runQuery) => $runQuery->where('status', $status));
                    }),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Tenant Overview')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('display_name')
                            ->label('Display Name'),
                        TextEntry::make('legal_name')
                            ->label('Legal Name')
                            ->placeholder('Not provided'),
                        TextEntry::make('slug')
                            ->copyable(),
                        TextEntry::make('state')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'active' => 'success',
                                'provisioning' => 'warning',
                                'suspended' => 'danger',
                                'archived' => 'gray',
                                default => 'gray',
                            }),
                        TextEntry::make('database_name')
                            ->label('Tenant Database')
                            ->copyable(),
                        TextEntry::make('region'),
                        TextEntry::make('timezone'),
                        TextEntry::make('locale')
                            ->placeholder('Not set'),
                        TextEntry::make('activated_at')
                            ->dateTime('M j, Y g:i A')
                            ->placeholder('Not activated yet'),
                        TextEntry::make('created_at')
                            ->dateTime('M j, Y g:i A'),
                    ]),
                Section::make('Primary Domain')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('primaryDomain.domain')
                            ->label('Domain')
                            ->copyable()
                            ->placeholder('No primary domain recorded'),
                        TextEntry::make('primaryDomain.kind')
                            ->label('Domain Type')
                            ->badge()
                            ->placeholder('Unknown'),
                        TextEntry::make('primaryDomain.is_verified')
                            ->label('Verification')
                            ->formatStateUsing(fn (?bool $state): string => match ($state) {
                                true => 'Verified',
                                false => 'Pending',
                                default => 'Unknown',
                            })
                            ->badge()
                            ->color(fn (?bool $state): string => match ($state) {
                                true => 'success',
                                false => 'warning',
                                default => 'gray',
                            }),
                    ]),
                Section::make('Latest Provisioning Run')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('latestProvisioningRun.status')
                            ->label('Run Status')
                            ->badge()
                            ->placeholder('No provisioning runs yet')
                            ->color(fn (?string $state): string => match ($state) {
                                'completed' => 'success',
                                'running' => 'warning',
                                'failed' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('latestProvisioningRun.current_step')
                            ->label('Current Step')
                            ->placeholder('Completed'),
                        TextEntry::make('latestProvisioningRun.triggeredByUser.email')
                            ->label('Triggered By')
                            ->placeholder('System or unknown'),
                        TextEntry::make('latestProvisioningRun.started_at')
                            ->label('Started At')
                            ->dateTime('M j, Y g:i A')
                            ->placeholder('No timestamp'),
                        TextEntry::make('latestProvisioningRun.finished_at')
                            ->label('Finished At')
                            ->dateTime('M j, Y g:i A')
                            ->placeholder('Still running'),
                        TextEntry::make('latestProvisioningRun.error_message')
                            ->label('Error Message')
                            ->placeholder('No error recorded')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            DomainsRelationManager::class,
            ProvisioningRunsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenants::route('/'),
            'view' => Pages\ViewTenant::route('/{record}'),
        ];
    }

    protected static function landlordUser(): ?LandlordUser
    {
        $user = auth()->user();

        return $user instanceof LandlordUser ? $user : null;
    }
}
