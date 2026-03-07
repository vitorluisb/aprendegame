<?php

namespace App\Filament\Resources\Paths\Schemas;

use App\Domain\Content\Models\Grade;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
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
                            ->live()
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

                        TextInput::make('bimester')
                            ->label('Bimestre')
                            ->integer()
                            ->minValue(1)
                            ->maxValue(4),

                        TextInput::make('order')
                            ->label('Ordem')
                            ->integer()
                            ->required()
                            ->minValue(1)
                            ->maxValue(fn (Get $get): int => self::resolveStagePathLimit((int) $get('grade_id')))
                            ->helperText(fn (Get $get): string => sprintf(
                                'Limite recomendado para esta etapa: %d missões por disciplina.',
                                self::resolveStagePathLimit((int) $get('grade_id'))
                            ))
                            ->default(1),

                        TextInput::make('xp_total')
                            ->label('XP Total')
                            ->integer()
                            ->required()
                            ->minValue(0)
                            ->default(0),

                        Select::make('unlocks_after_path_id')
                            ->label('Pré-requisito')
                            ->relationship('unlocksAfterPath', 'title')
                            ->searchable()
                            ->preload(),

                        Textarea::make('description')
                            ->label('Descrição')
                            ->rows(3)
                            ->columnSpanFull(),

                        TagsInput::make('bncc_skills')
                            ->label('Habilidades BNCC (códigos)')
                            ->placeholder('EF06MA01')
                            ->columnSpanFull(),

                        Select::make('path_type')
                            ->label('Tipo de Trilha')
                            ->options([
                                'regular' => 'Ensino Regular',
                                'enem' => 'ENEM',
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

    private static function resolveStagePathLimit(int $gradeId): int
    {
        if ($gradeId <= 0) {
            return 30;
        }

        $stage = Grade::query()->whereKey($gradeId)->value('stage');

        return match ($stage) {
            'fundamental_1' => 15,
            'fundamental_2' => 25,
            'medio' => 30,
            default => 30,
        };
    }
}
