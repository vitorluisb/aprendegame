<?php

namespace App\Filament\Resources\GkQuestions\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GkQuestionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category.name')->label('Categoria')->searchable()->sortable(),
                TextColumn::make('question_text')->label('Pergunta')->limit(70)->searchable(),
                TextColumn::make('difficulty')->label('Dificuldade')->badge(),
                IconColumn::make('is_active')->label('Ativa')->boolean(),
                TextColumn::make('created_at')->label('Criada em')->dateTime('d/m/Y H:i'),
            ])
            ->filters([
                SelectFilter::make('difficulty')
                    ->options([
                        'easy' => 'Fácil',
                        'medium' => 'Médio',
                        'hard' => 'Difícil',
                    ]),
                SelectFilter::make('category')->relationship('category', 'name'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
