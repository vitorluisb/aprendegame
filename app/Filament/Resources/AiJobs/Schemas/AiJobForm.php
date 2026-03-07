<?php

namespace App\Filament\Resources\AiJobs\Schemas;

use App\Domain\AI\Services\AIService;
use App\Domain\Content\Models\BnccSkill;
use App\Domain\Content\Models\Grade;
use App\Domain\Content\Models\Subject;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class AiJobForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Configuração da geração')
                    ->columns(2)
                    ->schema([
                        Select::make('grade_id')
                            ->label('Série')
                            ->options(fn (): array => Grade::query()->orderBy('order')->pluck('name', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('skill_id', null))
                            ->required(),

                        Select::make('subject_id')
                            ->label('Disciplina')
                            ->options(fn (): array => Subject::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('skill_id', null))
                            ->required(),

                        Select::make('type')
                            ->label('Tipo')
                            ->options([
                                'generate_questions' => 'Gerar questões',
                            ])
                            ->default('generate_questions')
                            ->required(),

                        Select::make('skill_id')
                            ->label('Habilidade BNCC')
                            ->disabled(fn (Get $get): bool => blank($get('grade_id')) || blank($get('subject_id')))
                            ->live()
                            ->options(function (Get $get): array {
                                $gradeId = $get('grade_id');
                                $subjectId = $get('subject_id');

                                if (! $gradeId || ! $subjectId) {
                                    return [];
                                }

                                return BnccSkill::query()
                                    ->where('grade_id', $gradeId)
                                    ->where('subject_id', $subjectId)
                                    ->where('active', true)
                                    ->orderBy('code')
                                    ->pluck('code', 'id')
                                    ->all();
                            })
                            ->searchable()
                            ->required(),

                        TextInput::make('requested_count')
                            ->label('Quantidade')
                            ->integer()
                            ->required()
                            ->default(10)
                            ->minValue(1)
                            ->maxValue(50),

                        TextInput::make('model')
                            ->label('Modelo')
                            ->required()
                            ->default(AIService::DEFAULT_MODEL),
                    ]),
            ]);
    }
}
