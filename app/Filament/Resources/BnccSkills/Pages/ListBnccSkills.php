<?php

namespace App\Filament\Resources\BnccSkills\Pages;

use App\Filament\Resources\BnccSkills\BnccSkillResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBnccSkills extends ListRecords
{
    protected static string $resource = BnccSkillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
