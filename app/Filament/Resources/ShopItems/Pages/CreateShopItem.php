<?php

namespace App\Filament\Resources\ShopItems\Pages;

use App\Domain\Gameplay\Models\ShopItem;
use App\Filament\Resources\ShopItems\ShopItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateShopItem extends CreateRecord
{
    protected static string $resource = ShopItemResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $avatarUpload = $data['avatar_upload'] ?? null;
        unset($data['avatar_upload']);

        if (($data['type'] ?? null) === ShopItem::TYPE_AVATAR && is_string($avatarUpload) && $avatarUpload !== '') {
            $data['image_url'] = '/media/'.$avatarUpload;
        }

        return $data;
    }
}
