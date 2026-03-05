<?php

namespace App\Filament\Resources\Questions\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class QuestionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Identificação')
                    ->columns(2)
                    ->schema([
                        Select::make('skill_id')
                            ->label('Habilidade BNCC')
                            ->relationship('skill', 'code')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Rascunho',
                                'reviewed' => 'Revisada',
                                'published' => 'Publicada',
                            ])
                            ->required()
                            ->default('draft'),

                        Select::make('type')
                            ->label('Tipo de Questão')
                            ->options([
                                'multiple_choice' => 'Múltipla Escolha',
                                'true_false' => 'Verdadeiro ou Falso',
                                'fill_blank' => 'Completar Lacuna',
                                'order_steps' => 'Ordenar Passos',
                                'drag_drop' => 'Arrastar e Soltar',
                                'short_answer' => 'Resposta Curta',
                            ])
                            ->required()
                            ->live(),

                        Select::make('difficulty')
                            ->label('Dificuldade')
                            ->options([
                                1 => '⭐ Muito Fácil',
                                2 => '⭐⭐ Fácil',
                                3 => '⭐⭐⭐ Médio',
                                4 => '⭐⭐⭐⭐ Difícil',
                                5 => '⭐⭐⭐⭐⭐ Muito Difícil',
                            ])
                            ->required()
                            ->default(2),

                        Toggle::make('ai_generated')
                            ->label('Gerada por IA')
                            ->default(false),
                    ]),

                Section::make('Conteúdo')
                    ->schema([
                        Textarea::make('prompt')
                            ->label('Enunciado')
                            ->required()
                            ->rows(4)
                            ->maxLength(2000),

                        Repeater::make('options')
                            ->label('Alternativas (somente para Múltipla Escolha)')
                            ->schema([
                                TextInput::make('key')
                                    ->label('Chave')
                                    ->required()
                                    ->maxLength(1)
                                    ->placeholder('A'),
                                TextInput::make('text')
                                    ->label('Texto da Alternativa')
                                    ->required()
                                    ->maxLength(500),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel('Adicionar alternativa')
                            ->helperText('Preencha apenas para questões de Múltipla Escolha.'),

                        TextInput::make('correct_answer')
                            ->label('Resposta Correta')
                            ->required()
                            ->maxLength(500)
                            ->helperText('Para múltipla escolha: a chave (ex: A). Para verdadeiro/falso: true ou false. Para lacuna: a palavra esperada.'),

                        Textarea::make('explanation')
                            ->label('Explicação (opcional)')
                            ->rows(3)
                            ->maxLength(1000),
                    ]),
            ]);
    }
}
