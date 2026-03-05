<?php

namespace App\Filament\Resources\PathNodes;

use App\Filament\Resources\PathNodes\Pages\CreatePathNode;
use App\Filament\Resources\PathNodes\Pages\EditPathNode;
use App\Filament\Resources\PathNodes\Pages\ListPathNodes;
use App\Filament\Resources\PathNodes\Schemas\PathNodeForm;
use App\Filament\Resources\PathNodes\Tables\PathNodesTable;
use App\Domain\Content\Models\PathNode;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PathNodeResource extends Resource
{
    protected static ?string $model = PathNode::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static string|\UnitEnum|null $navigationGroup = 'Conteúdo';

    protected static ?string $navigationLabel = 'Nós de Trilha';

    protected static ?string $modelLabel = 'nó';

    protected static ?string $pluralModelLabel = 'nós';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return PathNodeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PathNodesTable::configure($table);
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
            'index' => ListPathNodes::route('/'),
            'create' => CreatePathNode::route('/create'),
            'edit' => EditPathNode::route('/{record}/edit'),
        ];
    }
}
