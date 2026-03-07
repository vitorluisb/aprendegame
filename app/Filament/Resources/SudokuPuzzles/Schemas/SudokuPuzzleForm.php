<?php

namespace App\Filament\Resources\SudokuPuzzles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SudokuPuzzleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('difficulty')
                ->label('Dificuldade')
                ->options([
                    'easy' => 'Fácil',
                    'medium' => 'Médio',
                    'hard' => 'Difícil',
                ])
                ->required(),

            TextInput::make('puzzle_string')
                ->label('Puzzle (81 dígitos, use 0 para vazio)')
                ->minLength(81)
                ->maxLength(81)
                ->rule('regex:/^[0-9]{81}$/')
                ->required(),

            TextInput::make('solution_string')
                ->label('Solução (81 dígitos)')
                ->minLength(81)
                ->maxLength(81)
                ->rule('regex:/^[1-9]{81}$/')
                ->required(),

            TextInput::make('clues_count')
                ->label('Quantidade de pistas')
                ->integer()
                ->minValue(17)
                ->maxValue(81)
                ->required(),

            Toggle::make('is_active')
                ->label('Ativo')
                ->default(true)
                ->required(),
        ]);
    }
}
