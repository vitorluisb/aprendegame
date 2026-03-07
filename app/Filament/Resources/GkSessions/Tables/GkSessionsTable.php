<?php

namespace App\Filament\Resources\GkSessions\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class GkSessionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.email')->label('Usuário')->searchable(),
                TextColumn::make('status')->label('Status')->badge(),
                TextColumn::make('score')->label('Pontuação')->sortable(),
                TextColumn::make('correct_answers')->label('Acertos')->sortable(),
                TextColumn::make('current_round')->label('Rodada')->sortable(),
                TextColumn::make('reward_xp')->label('XP')->sortable(),
                TextColumn::make('reward_gems')->label('Neurons')->sortable(),
                TextColumn::make('started_at')->label('Início')->dateTime('d/m/Y H:i'),
                TextColumn::make('finished_at')->label('Fim')->dateTime('d/m/Y H:i')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'active' => 'Ativa',
                    'lost' => 'Perdida',
                    'completed' => 'Completa',
                ]),
            ]);
    }
}
