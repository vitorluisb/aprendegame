<?php

namespace App\Filament\Resources\Schools\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SchoolForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados da Escola')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Select::make('plan')
                            ->label('Plano')
                            ->options([
                                'basic' => 'Básico',
                                'pro' => 'Pro',
                                'enterprise' => 'Enterprise',
                            ])
                            ->required()
                            ->default('basic'),

                        Toggle::make('active')
                            ->label('Ativa')
                            ->default(true),

                        KeyValue::make('settings')
                            ->label('Configurações')
                            ->helperText('Use pares chave/valor para ajustes específicos da escola.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
