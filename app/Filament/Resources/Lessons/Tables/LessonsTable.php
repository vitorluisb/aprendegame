<?php

namespace App\Filament\Resources\Lessons\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class LessonsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('node.path.title')
                    ->label('Trilha')
                    ->sortable(),

                TextColumn::make('node.title')
                    ->label('Nó')
                    ->sortable(),

                TextColumn::make('difficulty')
                    ->label('Dificuldade')
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        1 => 'success',
                        2 => 'info',
                        3 => 'warning',
                        4, 5 => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (int $state): string => match ($state) {
                        1 => '★ Muito Fácil',
                        2 => '★★ Fácil',
                        3 => '★★★ Médio',
                        4 => '★★★★ Difícil',
                        5 => '★★★★★ Muito Difícil',
                        default => (string) $state,
                    }),

                TextColumn::make('interaction_count')
                    ->label('Questões/Sessão')
                    ->sortable(),

                ToggleColumn::make('published')
                    ->label('Publicada'),
            ])
            ->filters([
                SelectFilter::make('difficulty')
                    ->label('Dificuldade')
                    ->options([
                        1 => 'Muito Fácil',
                        2 => 'Fácil',
                        3 => 'Médio',
                        4 => 'Difícil',
                        5 => 'Muito Difícil',
                    ]),

                TernaryFilter::make('published')
                    ->label('Publicada')
                    ->trueLabel('Somente publicadas')
                    ->falseLabel('Somente não publicadas')
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
