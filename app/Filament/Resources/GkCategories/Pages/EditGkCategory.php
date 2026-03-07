<?php

namespace App\Filament\Resources\GkCategories\Pages;

use App\Filament\Resources\GkCategories\GkCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGkCategory extends EditRecord
{
    protected static string $resource = GkCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
