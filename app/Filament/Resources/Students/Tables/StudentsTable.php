<?php

namespace App\Filament\Resources\Students\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class StudentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.email')
                    ->label('Conta')
                    ->placeholder('Sem login')
                    ->searchable(),

                TextColumn::make('school.name')
                    ->label('Escola')
                    ->placeholder('Sem escola')
                    ->sortable(),

                TextColumn::make('grade.name')
                    ->label('Série')
                    ->placeholder('Não informada')
                    ->sortable(),

                TextColumn::make('birth_date')
                    ->label('Nascimento')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('xp_transactions_sum_amount')
                    ->label('XP Total')
                    ->sum('xpTransactions', 'amount')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('school')
                    ->label('Escola')
                    ->relationship('school', 'name'),

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
