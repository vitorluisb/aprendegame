<?php

namespace App\Filament\Resources\Questions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class QuestionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('prompt')
                    ->label('Enunciado')
                    ->limit(80)
                    ->searchable(),

                TextColumn::make('skill.code')
                    ->label('Habilidade')
                    ->badge()
                    ->color('info'),

                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'multiple_choice' => 'info',
                        'true_false' => 'success',
                        'fill_blank' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'multiple_choice' => 'Múlt. Escolha',
                        'true_false' => 'V ou F',
                        'fill_blank' => 'Lacuna',
                        'order_steps' => 'Ordenar',
                        'drag_drop' => 'Arrastar',
                        'short_answer' => 'Resp. Curta',
                        default => $state,
                    }),

                TextColumn::make('difficulty')
                    ->label('Dif.')
                    ->badge()
                    ->color(fn (int $state): string => match ($state) {
                        1 => 'success',
                        2 => 'info',
                        3 => 'warning',
                        4, 5 => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (int $state): string => str_repeat('★', $state)),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'reviewed' => 'warning',
                        'published' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft' => 'Rascunho',
                        'reviewed' => 'Revisada',
                        'published' => 'Publicada',
                        default => $state,
                    }),

                IconColumn::make('ai_generated')
                    ->label('IA')
                    ->boolean(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'multiple_choice' => 'Múltipla Escolha',
                        'true_false' => 'Verdadeiro ou Falso',
                        'fill_blank' => 'Completar Lacuna',
                        'order_steps' => 'Ordenar Passos',
                        'drag_drop' => 'Arrastar e Soltar',
                        'short_answer' => 'Resposta Curta',
                    ]),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Rascunho',
                        'reviewed' => 'Revisada',
                        'published' => 'Publicada',
                    ]),

                SelectFilter::make('difficulty')
                    ->label('Dificuldade')
                    ->options([
                        1 => 'Muito Fácil',
                        2 => 'Fácil',
                        3 => 'Médio',
                        4 => 'Difícil',
                        5 => 'Muito Difícil',
                    ]),

                TernaryFilter::make('ai_generated')
                    ->label('Gerada por IA')
                    ->trueLabel('Somente geradas por IA')
                    ->falseLabel('Somente criadas manualmente')
                    ->boolean(),
            ])
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
