<?php

namespace App\Filament\Resources\SchoolClasses\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SchoolClassForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados da Turma')
                    ->columns(2)
                    ->schema([
                        Select::make('school_id')
                            ->label('Escola')
                            ->relationship('school', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('grade_id')
                            ->label('Ano Escolar')
                            ->relationship('grade', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('name')
                            ->label('Nome da Turma')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('year')
                            ->label('Ano Letivo')
                            ->integer()
                            ->required()
                            ->minValue(2000)
                            ->maxValue(2100)
                            ->default((int) now()->format('Y')),

                        Toggle::make('active')
                            ->label('Ativa')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
