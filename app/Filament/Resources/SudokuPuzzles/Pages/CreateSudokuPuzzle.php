<?php

namespace App\Filament\Resources\SudokuPuzzles\Pages;

use App\Filament\Resources\SudokuPuzzles\SudokuPuzzleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSudokuPuzzle extends CreateRecord
{
    protected static string $resource = SudokuPuzzleResource::class;
}
