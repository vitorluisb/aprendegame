<?php

namespace App\Filament\Resources\Schools\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class SchoolsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('plan')
                    ->label('Plano')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'basic' => 'gray',
                        'pro' => 'info',
                        'enterprise' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'basic' => 'Básico',
                        'pro' => 'Pro',
                        'enterprise' => 'Enterprise',
                        default => $state,
                    }),

                TextColumn::make('students_count')
                    ->label('Alunos')
                    ->counts('students')
                    ->sortable(),

                ToggleColumn::make('active')
                    ->label('Ativa'),
            ])
            ->filters([
                SelectFilter::make('plan')
                    ->label('Plano')
                    ->options([
                        'basic' => 'Básico',
                        'pro' => 'Pro',
                        'enterprise' => 'Enterprise',
                    ]),

                TernaryFilter::make('active')
                    ->label('Ativa')
                    ->trueLabel('Somente ativas')
                    ->falseLabel('Somente inativas')
                    ->boolean(),

                TrashedFilter::make(),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
