<?php

namespace App\Filament\Resources\PathNodes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PathNodesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('path.title')
                    ->label('Trilha')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('order')
                    ->label('Ordem')
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Missão')
                    ->searchable(),

                TextColumn::make('node_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'lesson' => 'info',
                        'review' => 'warning',
                        'bonus' => 'success',
                        'boss' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'lesson' => 'Missão de Aula',
                        'review' => 'Revisão',
                        'bonus' => 'Bônus',
                        'boss' => 'Missão Final',
                        default => $state,
                    }),

                TextColumn::make('xp_reward')
                    ->label('XP')
                    ->sortable(),

                TextColumn::make('lessons_count')
                    ->label('Aulas')
                    ->counts('lessons')
                    ->sortable(),

                ToggleColumn::make('published')
                    ->label('Publicado'),
            ])
            ->filters([
                SelectFilter::make('path')
                    ->label('Trilha')
                    ->relationship('path', 'title'),

                SelectFilter::make('node_type')
                    ->label('Tipo da Missão')
                    ->options([
                        'lesson' => 'Missão de Aula',
                        'review' => 'Revisão',
                        'bonus' => 'Bônus',
                        'boss' => 'Missão Final',
                    ]),

                TernaryFilter::make('published')
                    ->label('Publicado')
                    ->trueLabel('Somente publicados')
                    ->falseLabel('Somente não publicados')
                    ->boolean(),
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
