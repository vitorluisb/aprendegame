<?php

namespace App\Filament\Resources\ShopItems\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ShopItemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'avatar' => 'info',
                        'frame' => 'warning',
                        'theme' => 'success',
                        'power_up' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'avatar' => 'Avatar',
                        'frame' => 'Moldura',
                        'theme' => 'Tema',
                        'power_up' => 'Power-up',
                        default => $state,
                    }),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('gem_price')
                    ->label('Gems')
                    ->sortable(),

                ToggleColumn::make('active')
                    ->label('Ativo'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'avatar' => 'Avatar',
                        'frame' => 'Moldura',
                        'theme' => 'Tema',
                        'power_up' => 'Power-up',
                    ]),

                TernaryFilter::make('active')
                    ->label('Ativo')
                    ->trueLabel('Somente ativos')
                    ->falseLabel('Somente inativos')
                    ->boolean(),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
