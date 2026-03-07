<?php

namespace App\Filament\Resources\DailyMissions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DailyMissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados da Missão')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('type')
                            ->label('Tipo')
                            ->options([
                                'study' => 'Estudo',
                                'streak' => 'Sequência',
                                'xp' => 'XP',
                                'perfect_lesson' => 'Aula perfeita',
                            ])
                            ->required(),

                        TextInput::make('target_value')
                            ->label('Meta')
                            ->integer()
                            ->required()
                            ->minValue(1),

                        TextInput::make('xp_reward')
                            ->label('Recompensa XP')
                            ->integer()
                            ->required()
                            ->minValue(1)
                            ->default(5),

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
