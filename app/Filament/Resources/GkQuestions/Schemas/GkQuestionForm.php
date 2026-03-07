<?php

namespace App\Filament\Resources\GkQuestions\Schemas;

use App\Domain\QuizMestre\Models\GkCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GkQuestionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('category_id')
                ->label('Categoria')
                ->options(fn (): array => GkCategory::query()->orderBy('name')->pluck('name', 'id')->all())
                ->searchable()
                ->required(),

            Textarea::make('question_text')->label('Pergunta')->required()->rows(3),

            TextInput::make('option_a')->label('Opção A')->required()->maxLength(500),
            TextInput::make('option_b')->label('Opção B')->required()->maxLength(500),
            TextInput::make('option_c')->label('Opção C')->required()->maxLength(500),
            TextInput::make('option_d')->label('Opção D')->required()->maxLength(500),

            Select::make('correct_option')
                ->label('Resposta correta')
                ->options([
                    'A' => 'A',
                    'B' => 'B',
                    'C' => 'C',
                    'D' => 'D',
                ])
                ->required(),

            Select::make('difficulty')
                ->label('Dificuldade')
                ->options([
                    'easy' => 'Fácil',
                    'medium' => 'Médio',
                    'hard' => 'Difícil',
                ])
                ->required(),

            TextInput::make('age_group')->label('Faixa etária')->maxLength(50),
            Textarea::make('explanation')->label('Explicação')->rows(2),
            TextInput::make('source_reference')->label('Referência')->maxLength(255),
            Toggle::make('is_active')->label('Ativa')->default(true),
        ]);
    }
}
