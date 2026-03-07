<?php

namespace App\Filament\Resources\GkCategories;

use App\Domain\QuizMestre\Models\GkCategory;
use App\Filament\Resources\GkCategories\Pages\CreateGkCategory;
use App\Filament\Resources\GkCategories\Pages\EditGkCategory;
use App\Filament\Resources\GkCategories\Pages\ListGkCategories;
use App\Filament\Resources\GkCategories\Schemas\GkCategoryForm;
use App\Filament\Resources\GkCategories\Tables\GkCategoriesTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GkCategoryResource extends Resource
{
    protected static ?string $model = GkCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|\UnitEnum|null $navigationGroup = 'Quiz Mestre';

    protected static ?string $navigationLabel = 'Categorias';

    protected static ?string $modelLabel = 'categoria';

    protected static ?string $pluralModelLabel = 'categorias';

    public static function form(Schema $schema): Schema
    {
        return GkCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GkCategoriesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGkCategories::route('/'),
            'create' => CreateGkCategory::route('/create'),
            'edit' => EditGkCategory::route('/{record}/edit'),
        ];
    }
}
