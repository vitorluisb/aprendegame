<?php

namespace App\Filament\Resources\Paths\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class PathsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('grade.name')
                    ->label('Ano Escolar')
                    ->sortable(),

                TextColumn::make('subject.name')
                    ->label('Disciplina')
                    ->sortable(),

                TextColumn::make('bimester')
                    ->label('Bimestre')
                    ->sortable(),

                TextColumn::make('order')
                    ->label('Ordem')
                    ->sortable(),

                TextColumn::make('path_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'regular' => 'info',
                        'enem' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'regular' => 'Regular',
                        'enem' => 'ENEM',
                        default => $state,
                    }),

                TextColumn::make('nodes_count')
                    ->label('Missões')
                    ->counts('nodes')
                    ->sortable(),

                TextColumn::make('xp_total')
                    ->label('XP Total')
                    ->sortable(),

                ToggleColumn::make('published')
                    ->label('Publicada'),
            ])
            ->filters([
                SelectFilter::make('path_type')
                    ->label('Tipo de Trilha')
                    ->options([
                        'regular' => 'Ensino Regular',
                        'enem' => 'ENEM',
                    ]),

                SelectFilter::make('grade')
                    ->label('Ano Escolar')
                    ->relationship('grade', 'name'),

                SelectFilter::make('subject')
                    ->label('Disciplina')
                    ->relationship('subject', 'name'),

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
