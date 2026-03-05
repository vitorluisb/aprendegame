<?php

namespace App\Filament\Resources\AiJobs\Pages;

use App\Filament\Resources\AiJobs\AiJobResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAiJob extends EditRecord
{
    protected static string $resource = AiJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
