<?php

namespace App\Filament\Resources\GkCategories\Pages;

use App\Filament\Resources\GkCategories\GkCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGkCategories extends ListRecords
{
    protected static string $resource = GkCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
