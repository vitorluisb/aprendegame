<?php

namespace App\Filament\Resources\SudokuPuzzles;

use App\Domain\Sudoku\Models\SudokuPuzzle;
use App\Filament\Resources\SudokuPuzzles\Pages\CreateSudokuPuzzle;
use App\Filament\Resources\SudokuPuzzles\Pages\EditSudokuPuzzle;
use App\Filament\Resources\SudokuPuzzles\Pages\ListSudokuPuzzles;
use App\Filament\Resources\SudokuPuzzles\Schemas\SudokuPuzzleForm;
use App\Filament\Resources\SudokuPuzzles\Tables\SudokuPuzzlesTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SudokuPuzzleResource extends Resource
{
    protected static ?string $model = SudokuPuzzle::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|\UnitEnum|null $navigationGroup = 'Jogos';

    protected static ?string $navigationLabel = 'Sudoku Puzzles';

    protected static ?string $modelLabel = 'sudoku puzzle';

    protected static ?string $pluralModelLabel = 'sudoku puzzles';

    public static function form(Schema $schema): Schema
    {
        return SudokuPuzzleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SudokuPuzzlesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSudokuPuzzles::route('/'),
            'create' => CreateSudokuPuzzle::route('/create'),
            'edit' => EditSudokuPuzzle::route('/{record}/edit'),
        ];
    }
}
