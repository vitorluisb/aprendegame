<?php

namespace App\Filament\Resources\BnccSkills\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BnccSkillsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Código')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('description')
                    ->label('Descrição')
                    ->limit(80)
                    ->searchable(),

                TextColumn::make('grade.name')
                    ->label('Ano Escolar')
                    ->sortable(),

                TextColumn::make('subject.name')
                    ->label('Disciplina')
                    ->sortable(),

                TextColumn::make('thematic_unit')
                    ->label('Unidade Temática')
                    ->toggleable(),

                TextColumn::make('knowledge_object')
                    ->label('Objeto de Conhecimento')
                    ->limit(40)
                    ->toggleable(),

                TextColumn::make('version')
                    ->label('Versão')
                    ->badge()
                    ->sortable(),

                ToggleColumn::make('active')
                    ->label('Ativa'),
            ])
            ->filters([
                SelectFilter::make('grade')
                    ->label('Ano Escolar')
                    ->relationship('grade', 'name'),

                SelectFilter::make('subject')
                    ->label('Disciplina')
                    ->relationship('subject', 'name'),

                TernaryFilter::make('active')
                    ->label('Ativa')
                    ->trueLabel('Somente ativas')
                    ->falseLabel('Somente inativas')
                    ->boolean(),
            ])
            ->defaultSort('code')
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
