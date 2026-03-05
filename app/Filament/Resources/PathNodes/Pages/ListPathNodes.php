<?php

namespace App\Filament\Resources\PathNodes\Pages;

use App\Filament\Resources\PathNodes\PathNodeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPathNodes extends ListRecords
{
    protected static string $resource = PathNodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
