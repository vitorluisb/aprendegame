<?php

namespace App\Filament\Resources\Badges\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BadgeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados da Conquista')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('icon')
                            ->label('Ícone')
                            ->maxLength(255),

                        Select::make('condition_type')
                            ->label('Tipo de Condição')
                            ->options([
                                'streak' => 'Sequência',
                                'xp' => 'XP acumulado',
                                'lesson' => 'Aulas concluídas',
                                'perfect_score' => 'Pontuação perfeita',
                            ])
                            ->required(),

                        TextInput::make('condition_value')
                            ->label('Valor da Condição')
                            ->integer()
                            ->required()
                            ->minValue(1),

                        Textarea::make('description')
                            ->label('Descrição')
                            ->rows(3)
                            ->columnSpanFull(),

                        Toggle::make('active')
                            ->label('Ativa')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
