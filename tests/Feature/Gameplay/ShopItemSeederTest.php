<?php

use App\Domain\Gameplay\Models\ShopItem;
use Database\Seeders\ShopItemSeeder;

it('seeds avatar shop items with valid image urls', function () {
    $this->seed(ShopItemSeeder::class);

    $avatarItems = ShopItem::query()
        ->where('type', 'avatar')
        ->orderBy('id')
        ->get();

    expect($avatarItems)->toHaveCount(4);

    foreach ($avatarItems as $item) {
        expect($item->image_url)->not->toBeNull();
        expect($item->image_url)->toStartWith('/shop-avatars/');
        expect(file_exists(public_path(ltrim((string) $item->image_url, '/'))))->toBeTrue();
    }
});

it('uses canonical shop item types without legacy aliases', function () {
    $this->seed(ShopItemSeeder::class);

    $types = ShopItem::query()->pluck('type')->unique()->values()->all();

    expect($types)->toContain('avatar');
    expect($types)->toContain('frame');
    expect($types)->toContain('theme');
    expect($types)->toContain('power_up');
    expect($types)->not->toContain('border');
    expect($types)->not->toContain('powerup');
});
