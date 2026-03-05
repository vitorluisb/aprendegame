<?php

namespace App\Filament\Resources\Grades\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GradesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('code')
                    ->label('Código')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                TextColumn::make('stage')
                    ->label('Etapa')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'fundamental' => 'info',
                        'medio' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'fundamental' => 'Ensino Fundamental',
                        'medio' => 'Ensino Médio',
                        default => $state,
                    }),

                TextColumn::make('order')
                    ->label('Ordem')
                    ->sortable(),

                TextColumn::make('skills_count')
                    ->label('Habilidades')
                    ->counts('skills')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('stage')
                    ->label('Etapa')
                    ->options([
                        'fundamental' => 'Ensino Fundamental',
                        'medio' => 'Ensino Médio',
                    ]),
            ])
            ->defaultSort('order')
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
