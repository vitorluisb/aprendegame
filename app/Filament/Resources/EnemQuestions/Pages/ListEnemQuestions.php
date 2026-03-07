<?php

namespace App\Filament\Resources\EnemQuestions\Pages;

use App\Domain\AI\Services\AIService;
use App\Domain\Enem\Services\EnemQuestionGenerationService;
use App\Filament\Resources\EnemQuestions\EnemQuestionResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListEnemQuestions extends ListRecords
{
    protected static string $resource = EnemQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('generateEnemBatch')
                ->label('Gerar lote ENEM')
                ->icon('heroicon-o-sparkles')
                ->form([
                    Select::make('area')
                        ->label('Área')
                        ->options([
                            'linguagens' => 'Linguagens',
                            'humanas' => 'Humanas',
                            'natureza' => 'Natureza',
                            'matematica' => 'Matemática',
                        ])
                        ->required(),
                    TextInput::make('subject')
                        ->label('Disciplina')
                        ->required(),
                    Select::make('difficulty')
                        ->label('Dificuldade')
                        ->options([
                            'easy' => 'Fácil',
                            'medium' => 'Médio',
                            'hard' => 'Difícil',
                        ])
                        ->default('medium')
                        ->required(),
                    TextInput::make('year_reference')
                        ->label('Ano referência')
                        ->numeric(),
                    TextInput::make('quantity')
                        ->label('Quantidade')
                        ->integer()
                        ->minValue(1)
                        ->maxValue(30)
                        ->default(10)
                        ->required(),
                    TextInput::make('model')
                        ->label('Modelo IA')
                        ->default(AIService::DEFAULT_MODEL)
                        ->required(),
                ])
                ->action(function (array $data, EnemQuestionGenerationService $service): void {
                    $created = $service->generateAndStore(
                        (string) $data['area'],
                        (string) $data['subject'],
                        (int) $data['quantity'],
                        (string) $data['difficulty'],
                        isset($data['year_reference']) ? (int) $data['year_reference'] : null,
                        (string) $data['model'],
                    );

                    Notification::make()
                        ->title(count($created).' questões ENEM geradas.')
                        ->success()
                        ->send();
                }),
        ];
    }
}
