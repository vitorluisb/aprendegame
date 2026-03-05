<?php

namespace App\Filament\Resources\ShopItems;

use App\Filament\Resources\ShopItems\Pages\CreateShopItem;
use App\Filament\Resources\ShopItems\Pages\EditShopItem;
use App\Filament\Resources\ShopItems\Pages\ListShopItems;
use App\Filament\Resources\ShopItems\Schemas\ShopItemForm;
use App\Filament\Resources\ShopItems\Tables\ShopItemsTable;
use App\Domain\Gameplay\Models\ShopItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ShopItemResource extends Resource
{
    protected static ?string $model = ShopItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static string|\UnitEnum|null $navigationGroup = 'Gamificação';

    protected static ?string $navigationLabel = 'Loja de Itens';

    protected static ?string $modelLabel = 'item da loja';

    protected static ?string $pluralModelLabel = 'itens da loja';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return ShopItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShopItemsTable::configure($table);
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
            'index' => ListShopItems::route('/'),
            'create' => CreateShopItem::route('/create'),
            'edit' => EditShopItem::route('/{record}/edit'),
        ];
    }
}
