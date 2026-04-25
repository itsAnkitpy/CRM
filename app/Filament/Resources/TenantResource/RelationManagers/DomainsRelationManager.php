<?php

namespace App\Filament\Resources\TenantResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DomainsRelationManager extends RelationManager
{
    protected static string $relationship = 'domains';

    protected static ?string $title = 'Domains';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('domain')
            ->defaultSort('is_primary', 'desc')
            ->columns([
                TextColumn::make('domain')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('kind')
                    ->badge(),
                TextColumn::make('is_primary')
                    ->label('Primary')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Yes' : 'No')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                TextColumn::make('is_verified')
                    ->label('Verified')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Yes' : 'No')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'warning'),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M j, Y g:i A'),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
