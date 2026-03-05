<?php

namespace App\Filament\Resources\AiJobs\Pages;

use App\Filament\Resources\AiJobs\AiJobResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAiJobs extends ListRecords
{
    protected static string $resource = AiJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
