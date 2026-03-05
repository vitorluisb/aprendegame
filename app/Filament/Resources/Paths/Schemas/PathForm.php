<?php

namespace App\Filament\Resources\Paths\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PathForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informações da Trilha')
                    ->columns(2)
                    ->schema([
                        Select::make('grade_id')
                            ->label('Ano Escolar')
                            ->relationship('grade', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('subject_id')
                            ->label('Disciplina')
                            ->relationship('subject', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        TextInput::make('title')
                            ->label('Título')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('path_type')
                            ->label('Tipo de Trilha')
                            ->options([
                                'regular' => 'Ensino Regular',
                                'enem' => 'ENEM',
                                'vestibular_fuvest' => 'Vestibular FUVEST',
                                'vestibular_unicamp' => 'Vestibular UNICAMP',
                            ])
                            ->required()
                            ->default('regular'),

                        Toggle::make('published')
                            ->label('Publicada')
                            ->helperText('Apenas trilhas publicadas aparecem para os alunos.')
                            ->default(false),
                    ]),
            ]);
    }
}
