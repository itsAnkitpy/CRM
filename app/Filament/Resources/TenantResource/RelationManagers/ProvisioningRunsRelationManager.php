<?php

namespace App\Filament\Resources\TenantResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProvisioningRunsRelationManager extends RelationManager
{
    protected static string $relationship = 'provisioningRuns';

    protected static ?string $title = 'Provisioning Runs';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'running' => 'warning',
                        'failed' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('current_step')
                    ->label('Current Step')
                    ->placeholder('Completed'),
                TextColumn::make('triggeredByUser.email')
                    ->label('Triggered By')
                    ->placeholder('System or unknown'),
                TextColumn::make('started_at')
                    ->label('Started')
                    ->dateTime('M j, Y g:i A'),
                TextColumn::make('finished_at')
                    ->label('Finished')
                    ->dateTime('M j, Y g:i A')
                    ->placeholder('Still running'),
                TextColumn::make('error_message')
                    ->label('Error')
                    ->limit(80)
                    ->tooltip(fn ($state) => filled($state) ? $state : null)
                    ->placeholder('No error recorded'),
            ])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
