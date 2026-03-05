<?php

namespace App\Filament\Resources\Subjects\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SubjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados da Disciplina')
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

                        TextInput::make('icon')
                            ->label('Ícone')
                            ->maxLength(255),

                        TextInput::make('color')
                            ->label('Cor (hex)')
                            ->maxLength(7)
                            ->regex('/^#[0-9A-Fa-f]{6}$/')
                            ->helperText('Exemplo: #3B82F6'),
                    ]),
            ]);
    }
}
