<?php

namespace App\Filament\Resources\Grades\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class GradeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Ano Escolar')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('code')
                            ->label('Código')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Select::make('stage')
                            ->label('Etapa')
                            ->options([
                                'fundamental' => 'Ensino Fundamental',
                                'medio' => 'Ensino Médio',
                            ])
                            ->required(),

                        TextInput::make('order')
                            ->label('Ordem')
                            ->integer()
                            ->required()
                            ->minValue(1),
                    ]),
            ]);
    }
}
