<?php

namespace App\Filament\Resources\ShopItems\Pages;

use App\Domain\Gameplay\Models\ShopItem;
use App\Filament\Resources\ShopItems\ShopItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditShopItem extends EditRecord
{
    protected static string $resource = ShopItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (($data['type'] ?? null) !== ShopItem::TYPE_AVATAR) {
            return $data;
        }

        $avatarStoragePath = ShopItem::extractAvatarStoragePath($data['image_url'] ?? null);
        if (is_string($avatarStoragePath) && $avatarStoragePath !== '') {
            $data['avatar_upload'] = $avatarStoragePath;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $avatarUpload = $data['avatar_upload'] ?? null;
        unset($data['avatar_upload']);

        if (($data['type'] ?? null) === ShopItem::TYPE_AVATAR) {
            if (is_string($avatarUpload) && $avatarUpload !== '') {
                $data['image_url'] = '/media/'.$avatarUpload;
            } else {
                $data['image_url'] = $this->record->image_url;
            }
        }

        return $data;
    }
}
