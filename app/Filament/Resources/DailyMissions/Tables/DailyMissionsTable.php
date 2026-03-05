<?php

namespace App\Filament\Resources\DailyMissions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class DailyMissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'study' => 'info',
                        'streak' => 'warning',
                        'xp' => 'success',
                        'perfect_lesson' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'study' => 'Estudo',
                        'streak' => 'Sequência',
                        'xp' => 'XP',
                        'perfect_lesson' => 'Aula perfeita',
                        default => $state,
                    }),

                TextColumn::make('target_value')
                    ->label('Meta')
                    ->sortable(),

                TextColumn::make('xp_reward')
                    ->label('XP')
                    ->sortable(),

                TextColumn::make('student_missions_count')
                    ->label('Concluída')
                    ->counts('studentMissions')
                    ->sortable(),

                ToggleColumn::make('active')
                    ->label('Ativa'),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'study' => 'Estudo',
                        'streak' => 'Sequência',
                        'xp' => 'XP',
                        'perfect_lesson' => 'Aula perfeita',
                    ]),

                TernaryFilter::make('active')
                    ->label('Ativa')
                    ->trueLabel('Somente ativas')
                    ->falseLabel('Somente inativas')
                    ->boolean(),
            ])
            ->defaultSort('title')
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
