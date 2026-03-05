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

                TextColumn::make('path_type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'regular' => 'info',
                        'enem' => 'warning',
                        'vestibular_fuvest', 'vestibular_unicamp' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'regular' => 'Regular',
                        'enem' => 'ENEM',
                        'vestibular_fuvest' => 'FUVEST',
                        'vestibular_unicamp' => 'UNICAMP',
                        default => $state,
                    }),

                TextColumn::make('nodes_count')
                    ->label('Nós')
                    ->counts('nodes')
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
                        'vestibular_fuvest' => 'FUVEST',
                        'vestibular_unicamp' => 'UNICAMP',
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
