<?php

namespace App\Filament\Resources\Badges\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BadgesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('icon')
                    ->label('Ícone'),

                TextColumn::make('condition_type')
                    ->label('Condição')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'streak' => 'warning',
                        'xp' => 'info',
                        'lesson' => 'success',
                        'perfect_score' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'streak' => 'Sequência',
                        'xp' => 'XP',
                        'lesson' => 'Aulas',
                        'perfect_score' => 'Pontuação perfeita',
                        default => $state,
                    }),

                TextColumn::make('condition_value')
                    ->label('Meta')
                    ->sortable(),

                TextColumn::make('students_count')
                    ->label('Concedida')
                    ->counts('students')
                    ->sortable(),

                ToggleColumn::make('active')
                    ->label('Ativa'),
            ])
            ->filters([
                SelectFilter::make('condition_type')
                    ->label('Tipo de Condição')
                    ->options([
                        'streak' => 'Sequência',
                        'xp' => 'XP acumulado',
                        'lesson' => 'Aulas concluídas',
                        'perfect_score' => 'Pontuação perfeita',
                    ]),

                TernaryFilter::make('active')
                    ->label('Ativa')
                    ->trueLabel('Somente ativas')
                    ->falseLabel('Somente inativas')
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
