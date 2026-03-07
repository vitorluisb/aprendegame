<?php

namespace App\Filament\Resources\GkQuestions\Pages;

use App\Domain\AI\Services\AIService;
use App\Domain\QuizMestre\Models\GkCategory;
use App\Domain\QuizMestre\Services\QuizMestreQuestionGenerationService;
use App\Filament\Resources\GkQuestions\GkQuestionResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListGkQuestions extends ListRecords
{
    protected static string $resource = GkQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('generateBatch')
                ->label('Gerar lote')
                ->icon('heroicon-o-sparkles')
                ->form([
                    Select::make('category_id')
                        ->label('Categoria')
                        ->options(fn (): array => GkCategory::query()->orderBy('name')->pluck('name', 'id')->all())
                        ->searchable()
                        ->required(),
                    Select::make('difficulty')
                        ->label('Dificuldade')
                        ->options([
                            'easy' => 'Fácil',
                            'medium' => 'Médio',
                            'hard' => 'Difícil',
                        ])
                        ->required(),
                    TextInput::make('quantity')
                        ->label('Quantidade')
                        ->integer()
                        ->minValue(1)
                        ->maxValue(100)
                        ->default(20)
                        ->required(),
                    TextInput::make('model')
                        ->label('Modelo IA')
                        ->default(AIService::DEFAULT_MODEL)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $category = GkCategory::query()->findOrFail((int) $data['category_id']);
                    $generationService = app(QuizMestreQuestionGenerationService::class);
                    $createdCount = $generationService->generateForCategory(
                        category: $category,
                        difficulty: (string) $data['difficulty'],
                        quantity: (int) $data['quantity'],
                        model: (string) ($data['model'] ?? AIService::DEFAULT_MODEL),
                    );

                    Notification::make()
                        ->title("Lote criado com {$createdCount} questões por IA.")
                        ->success()
                        ->send();
                }),
        ];
    }
}
