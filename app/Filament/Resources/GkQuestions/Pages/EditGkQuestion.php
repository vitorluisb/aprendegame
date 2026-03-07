<?php

namespace App\Filament\Resources\GkQuestions\Pages;

use App\Filament\Resources\GkQuestions\GkQuestionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGkQuestion extends EditRecord
{
    protected static string $resource = GkQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
