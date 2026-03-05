<?php

namespace App\Filament\Resources\PathNodes\Schemas;

use App\Domain\Content\Models\BnccSkill;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PathNodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Nó')
                    ->columns(2)
                    ->schema([
                        Select::make('path_id')
                            ->label('Trilha')
                            ->relationship('path', 'title')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('title')
                            ->label('Título do Nó')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('order')
                            ->label('Ordem')
                            ->integer()
                            ->required()
                            ->minValue(1),

                        Select::make('node_type')
                            ->label('Tipo do Nó')
                            ->options([
                                'lesson' => 'Aulas',
                                'boss' => 'Desafio Final',
                            ])
                            ->required()
                            ->default('lesson'),

                        Select::make('skill_ids')
                            ->label('Habilidades BNCC')
                            ->multiple()
                            ->options(fn () => BnccSkill::orderBy('code')->pluck('code', 'id'))
                            ->searchable()
                            ->helperText('Habilidades BNCC cobertas por este nó.')
                            ->columnSpanFull(),

                        Toggle::make('published')
                            ->label('Publicado')
                            ->helperText('Apenas nós publicados aparecem para os alunos.')
                            ->default(false),
                    ]),
            ]);
    }
}
