<?php

namespace App\Filament\Resources\BnccSkills\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BnccSkillForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados da Habilidade BNCC')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Código')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        TextInput::make('version')
                            ->label('Versão')
                            ->integer()
                            ->required()
                            ->minValue(1)
                            ->default(1),

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

                        Textarea::make('description')
                            ->label('Descrição')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),

                        TextInput::make('thematic_unit')
                            ->label('Unidade Temática')
                            ->maxLength(255),

                        TextInput::make('knowledge_object')
                            ->label('Objeto de Conhecimento')
                            ->maxLength(255),

                        Toggle::make('active')
                            ->label('Ativa')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
