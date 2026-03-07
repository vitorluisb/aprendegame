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
                Section::make('Dados da Missão')
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
                            ->label('Título da Missão')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('order')
                            ->label('Ordem')
                            ->integer()
                            ->required()
                            ->minValue(1),

                        TextInput::make('xp_reward')
                            ->label('XP da Parada')
                            ->integer()
                            ->required()
                            ->minValue(0)
                            ->default(100),

                        Select::make('node_type')
                            ->label('Tipo da Missão')
                            ->options([
                                'lesson' => 'Missão de Aula',
                                'review' => 'Revisão',
                                'bonus' => 'Bônus',
                                'boss' => 'Missão Final',
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
                            ->helperText('Apenas missões publicadas aparecem para os alunos.')
                            ->default(false),
                    ]),
            ]);
    }
}
