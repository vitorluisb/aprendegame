<?php

namespace App\Filament\Resources\EnemQuestions\Pages;

use App\Filament\Resources\EnemQuestions\EnemQuestionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEnemQuestion extends EditRecord
{
    protected static string $resource = EnemQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
