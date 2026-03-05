<?php

namespace App\Filament\Resources\PathNodes\Pages;

use App\Filament\Resources\PathNodes\PathNodeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPathNode extends EditRecord
{
    protected static string $resource = PathNodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
