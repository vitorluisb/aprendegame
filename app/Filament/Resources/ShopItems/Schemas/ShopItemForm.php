<?php

namespace App\Filament\Resources\ShopItems\Schemas;

use App\Domain\Gameplay\Models\ShopItem;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
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
                            ->live()
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

                        FileUpload::make('avatar_upload')
                            ->label('Arquivo do Avatar')
                            ->acceptedFileTypes([
                                'image/gif',
                                'image/png',
                                'image/jpeg',
                                'image/webp',
                                'image/svg+xml',
                                'video/mp4',
                            ])
                            ->disk('public')
                            ->directory('shop-avatars')
                            ->visibility('public')
                            ->columnSpanFull()
                            ->visible(fn (Get $get): bool => $get('type') === ShopItem::TYPE_AVATAR)
                            ->helperText('Envie GIF/PNG/JPG/WEBP/SVG ou MP4 para avatar (será servido em /media/shop-avatars).'),

                        TextInput::make('image_url')
                            ->label('URL da Imagem')
                            ->url()
                            ->maxLength(255)
                            ->visible(fn (Get $get): bool => $get('type') !== ShopItem::TYPE_AVATAR)
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
