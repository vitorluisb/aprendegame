<?php

namespace App\Filament\Resources\SudokuPuzzles\Pages;

use App\Filament\Resources\SudokuPuzzles\SudokuPuzzleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSudokuPuzzle extends EditRecord
{
    protected static string $resource = SudokuPuzzleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
