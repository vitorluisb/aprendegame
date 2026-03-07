<?php

namespace App\Filament\Resources\SudokuPuzzles\Pages;

use App\Filament\Resources\SudokuPuzzles\SudokuPuzzleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSudokuPuzzles extends ListRecords
{
    protected static string $resource = SudokuPuzzleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
