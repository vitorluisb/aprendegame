<?php

namespace App\Filament\Resources\EnemQuestions\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EnemQuestionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Identificação')
                ->columns(2)
                ->schema([
                    Select::make('area')
                        ->label('Área ENEM')
                        ->options([
                            'linguagens' => 'Linguagens, Códigos e suas Tecnologias',
                            'humanas' => 'Ciências Humanas e suas Tecnologias',
                            'natureza' => 'Ciências da Natureza e suas Tecnologias',
                            'matematica' => 'Matemática e suas Tecnologias',
                        ])
                        ->required(),
                    TextInput::make('subject')
                        ->label('Disciplina')
                        ->required()
                        ->maxLength(120),
                    TextInput::make('enem_code')
                        ->label('Código ENEM')
                        ->helperText('Deixe vazio para gerar automaticamente.')
                        ->maxLength(32),
                    Select::make('difficulty')
                        ->label('Dificuldade')
                        ->options([
                            'easy' => 'Fácil',
                            'medium' => 'Médio',
                            'hard' => 'Difícil',
                        ])
                        ->required(),
                    TextInput::make('year_reference')
                        ->label('Ano de referência')
                        ->numeric()
                        ->minValue(2009)
                        ->maxValue((int) now()->year + 1),
                    Select::make('status')
                        ->label('Status')
                        ->options([
                            'draft' => 'Rascunho',
                            'reviewed' => 'Revisada',
                            'approved' => 'Aprovada',
                        ])
                        ->default('draft')
                        ->required(),
                    Toggle::make('is_active')
                        ->label('Ativa')
                        ->default(true)
                        ->required(),
                ]),
            Section::make('Estrutura ENEM')
                ->schema([
                    Textarea::make('context_text')
                        ->label('Texto contextual (3-8 linhas)')
                        ->rows(6)
                        ->required(),
                    Textarea::make('question_text')
                        ->label('Pergunta')
                        ->rows(3)
                        ->required(),
                    Textarea::make('option_a')->label('Alternativa A')->rows(2)->required(),
                    Textarea::make('option_b')->label('Alternativa B')->rows(2)->required(),
                    Textarea::make('option_c')->label('Alternativa C')->rows(2)->required(),
                    Textarea::make('option_d')->label('Alternativa D')->rows(2)->required(),
                    Textarea::make('option_e')->label('Alternativa E')->rows(2)->required(),
                    Select::make('correct_option')
                        ->label('Alternativa correta')
                        ->options([
                            'A' => 'A',
                            'B' => 'B',
                            'C' => 'C',
                            'D' => 'D',
                            'E' => 'E',
                        ])
                        ->required(),
                    Textarea::make('explanation')
                        ->label('Explicação')
                        ->rows(3)
                        ->required(),
                ]),
        ]);
    }
}
