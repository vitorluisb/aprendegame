<?php

namespace App\Filament\Resources\Paths;

use App\Filament\Resources\Paths\Pages\CreatePath;
use App\Filament\Resources\Paths\Pages\EditPath;
use App\Filament\Resources\Paths\Pages\ListPaths;
use App\Filament\Resources\Paths\Schemas\PathForm;
use App\Filament\Resources\Paths\Tables\PathsTable;
use App\Domain\Content\Models\Path;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PathResource extends Resource
{
    protected static ?string $model = Path::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMap;

    protected static string|\UnitEnum|null $navigationGroup = 'Conteúdo';

    protected static ?string $navigationLabel = 'Trilhas';

    protected static ?string $modelLabel = 'trilha';

    protected static ?string $pluralModelLabel = 'trilhas';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return PathForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PathsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaths::route('/'),
            'create' => CreatePath::route('/create'),
            'edit' => EditPath::route('/{record}/edit'),
        ];
    }
}
