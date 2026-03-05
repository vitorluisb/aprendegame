<?php

namespace App\Filament\Resources\BnccSkills\Pages;

use App\Filament\Resources\BnccSkills\BnccSkillResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBnccSkill extends EditRecord
{
    protected static string $resource = BnccSkillResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
