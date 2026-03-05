<?php

namespace App\Filament\Resources\SchoolClasses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SchoolClassesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Turma')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('school.name')
                    ->label('Escola')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('grade.name')
                    ->label('Ano Escolar')
                    ->sortable(),

                TextColumn::make('year')
                    ->label('Ano Letivo')
                    ->sortable(),

                TextColumn::make('students_count')
                    ->label('Alunos')
                    ->counts('students')
                    ->sortable(),

                ToggleColumn::make('active')
                    ->label('Ativa'),
            ])
            ->filters([
                SelectFilter::make('school')
                    ->label('Escola')
                    ->relationship('school', 'name'),

                SelectFilter::make('grade')
                    ->label('Ano Escolar')
                    ->relationship('grade', 'name'),

                TernaryFilter::make('active')
                    ->label('Ativa')
                    ->trueLabel('Somente ativas')
                    ->falseLabel('Somente inativas')
                    ->boolean(),
            ])
            ->defaultSort('year', 'desc')
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
