<?php

namespace App\Filament\Resources\ShopItems\Schemas;

use App\Domain\Gameplay\Models\ShopItem;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ShopItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dados do Item da Loja')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('type')
                            ->label('Tipo')
                            ->options([
                                ShopItem::TYPE_AVATAR => 'Avatar',
                                ShopItem::TYPE_FRAME => 'Moldura',
                                ShopItem::TYPE_THEME => 'Tema',
                                ShopItem::TYPE_POWER_UP => 'Power-up',
                            ])
                            ->required(),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        TextInput::make('gem_price')
                            ->label('Preço em Gems')
                            ->integer()
                            ->required()
                            ->minValue(0),

                        TextInput::make('image_url')
                            ->label('URL da Imagem')
                            ->url()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Descrição')
                            ->rows(3)
                            ->columnSpanFull(),

                        KeyValue::make('metadata')
                            ->label('Metadados')
                            ->columnSpanFull(),

                        Toggle::make('active')
                            ->label('Ativo')
                            ->default(true)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
