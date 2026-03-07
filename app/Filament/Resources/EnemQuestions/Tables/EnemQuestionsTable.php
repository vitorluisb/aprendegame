<?php

namespace App\Filament\Resources\EnemQuestions\Tables;

use App\Domain\Enem\Models\EnemQuestion;
use Filament\Actions\Action;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EnemQuestionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('enem_code')->label('Código')->searchable(),
                TextColumn::make('area')->label('Área')->badge(),
                TextColumn::make('subject')->label('Disciplina')->searchable(),
                TextColumn::make('difficulty')->label('Dificuldade')->badge(),
                TextColumn::make('year_reference')->label('Ano')->sortable(),
                TextColumn::make('status')->label('Status')->badge(),
                IconColumn::make('is_active')->label('Ativa')->boolean(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Tipo')
                    ->options([
                        'enem' => 'ENEM',
                        'regular' => 'Regular',
                    ])
                    ->default('enem')
                    ->query(function ($query, array $data) {
                        if (($data['value'] ?? 'enem') === 'regular') {
                            $query->whereRaw('1 = 0');
                        }
                    }),
                SelectFilter::make('area')
                    ->options([
                        'linguagens' => 'Linguagens',
                        'humanas' => 'Humanas',
                        'natureza' => 'Natureza',
                        'matematica' => 'Matemática',
                    ]),
                SelectFilter::make('subject')
                    ->options(fn (): array => EnemQuestion::query()
                        ->select('subject')
                        ->distinct()
                        ->orderBy('subject')
                        ->pluck('subject', 'subject')
                        ->all()),
                SelectFilter::make('difficulty')
                    ->options([
                        'easy' => 'Fácil',
                        'medium' => 'Médio',
                        'hard' => 'Difícil',
                    ]),
                SelectFilter::make('year_reference')
                    ->options(fn (): array => EnemQuestion::query()
                        ->whereNotNull('year_reference')
                        ->select('year_reference')
                        ->distinct()
                        ->orderByDesc('year_reference')
                        ->pluck('year_reference', 'year_reference')
                        ->mapWithKeys(fn ($year): array => [(string) $year => (string) $year])
                        ->all()),
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Rascunho',
                        'reviewed' => 'Revisada',
                        'approved' => 'Aprovada',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                Action::make('review')
                    ->label('Revisar')
                    ->color('warning')
                    ->action(fn ($record) => $record->update(['status' => 'reviewed'])),
                Action::make('approve')
                    ->label('Aprovar')
                    ->color('success')
                    ->action(fn ($record) => $record->update(['status' => 'approved'])),
                Action::make('deactivate')
                    ->label('Desativar')
                    ->color('danger')
                    ->action(fn ($record) => $record->update(['is_active' => false])),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
