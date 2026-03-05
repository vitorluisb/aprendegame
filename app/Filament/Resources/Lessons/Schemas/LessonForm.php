<?php

namespace App\Filament\Resources\Lessons\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LessonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados da Aula')
                    ->columns(2)
                    ->schema([
                        Select::make('node_id')
                            ->label('Nó da Trilha')
                            ->relationship('node', 'title')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('title')
                            ->label('Título da Aula')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('difficulty')
                            ->label('Dificuldade')
                            ->options([
                                1 => '⭐ Muito Fácil',
                                2 => '⭐⭐ Fácil',
                                3 => '⭐⭐⭐ Médio',
                                4 => '⭐⭐⭐⭐ Difícil',
                                5 => '⭐⭐⭐⭐⭐ Muito Difícil',
                            ])
                            ->required()
                            ->default(2),

                        TextInput::make('interaction_count')
                            ->label('Qtd. de Questões')
                            ->integer()
                            ->required()
                            ->minValue(1)
                            ->maxValue(50)
                            ->default(10)
                            ->helperText('Número de questões apresentadas por sessão.'),

                        Toggle::make('published')
                            ->label('Publicada')
                            ->helperText('Apenas aulas publicadas aparecem para os alunos.')
                            ->default(false)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
