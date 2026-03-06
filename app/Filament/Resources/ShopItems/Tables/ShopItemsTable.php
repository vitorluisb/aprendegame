<?php

namespace App\Filament\Resources\ShopItems\Tables;

use App\Domain\Gameplay\Models\ShopItem;
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
                        ShopItem::TYPE_AVATAR => 'info',
                        ShopItem::TYPE_FRAME => 'warning',
                        ShopItem::TYPE_THEME => 'success',
                        ShopItem::TYPE_POWER_UP => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        ShopItem::TYPE_AVATAR => 'Avatar',
                        ShopItem::TYPE_FRAME => 'Moldura',
                        ShopItem::TYPE_THEME => 'Tema',
                        ShopItem::TYPE_POWER_UP => 'Power-up',
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
                        ShopItem::TYPE_AVATAR => 'Avatar',
                        ShopItem::TYPE_FRAME => 'Moldura',
                        ShopItem::TYPE_THEME => 'Tema',
                        ShopItem::TYPE_POWER_UP => 'Power-up',
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
