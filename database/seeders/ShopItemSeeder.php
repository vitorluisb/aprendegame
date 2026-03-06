<?php

namespace Database\Seeders;

use App\Domain\Gameplay\Models\ShopItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class ShopItemSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            // Avatares
            [
                'name' => 'Avatar Astronauta',
                'type' => ShopItem::TYPE_AVATAR,
                'slug' => 'avatar-astronauta',
                'description' => 'Vista-se como um astronauta explorador do conhecimento.',
                'image_url' => '/shop-avatars/avatar-astronauta.svg',
                'gem_price' => 50,
                'active' => true,
                'metadata' => ['image' => 'avatars/astronauta.png', 'rarity' => 'common'],
            ],
            [
                'name' => 'Avatar Cientista',
                'type' => ShopItem::TYPE_AVATAR,
                'slug' => 'avatar-cientista',
                'description' => 'Mostre seu lado científico com este avatar exclusivo.',
                'image_url' => '/shop-avatars/avatar-cientista.svg',
                'gem_price' => 75,
                'active' => true,
                'metadata' => ['image' => 'avatars/cientista.png', 'rarity' => 'common'],
            ],
            [
                'name' => 'Avatar Ninja',
                'type' => ShopItem::TYPE_AVATAR,
                'slug' => 'avatar-ninja',
                'description' => 'Seja veloz e preciso como um ninja do conhecimento.',
                'image_url' => '/shop-avatars/avatar-ninja.svg',
                'gem_price' => 100,
                'active' => true,
                'metadata' => ['image' => 'avatars/ninja.png', 'rarity' => 'rare'],
            ],
            [
                'name' => 'Avatar Dragão Sábio',
                'type' => ShopItem::TYPE_AVATAR,
                'slug' => 'avatar-dragao-sabio',
                'description' => 'O dragão mais sábio de todas as terras digitais.',
                'image_url' => '/shop-avatars/avatar-dragao-sabio.svg',
                'gem_price' => 200,
                'active' => true,
                'metadata' => ['image' => 'avatars/dragao.png', 'rarity' => 'epic'],
            ],
            // Bordas
            [
                'name' => 'Borda Dourada',
                'type' => ShopItem::TYPE_FRAME,
                'slug' => 'borda-dourada',
                'description' => 'Uma borda dourada para destacar seu perfil.',
                'gem_price' => 30,
                'active' => true,
                'metadata' => ['color' => '#FFD700', 'style' => 'solid'],
            ],
            [
                'name' => 'Borda Arco-íris',
                'type' => ShopItem::TYPE_FRAME,
                'slug' => 'borda-arco-iris',
                'description' => 'Uma borda colorida e animada para o seu perfil.',
                'gem_price' => 80,
                'active' => true,
                'metadata' => ['style' => 'animated', 'rarity' => 'rare'],
            ],
            [
                'name' => 'Borda de Fogo',
                'type' => ShopItem::TYPE_FRAME,
                'slug' => 'borda-fogo',
                'description' => 'Mostre que você está em chamas com esta borda épica.',
                'gem_price' => 150,
                'active' => true,
                'metadata' => ['style' => 'animated', 'rarity' => 'epic'],
            ],
            // Temas
            [
                'name' => 'Tema Noturno',
                'type' => ShopItem::TYPE_THEME,
                'slug' => 'tema-noturno',
                'description' => 'Estude com estilo com o tema escuro exclusivo.',
                'gem_price' => 60,
                'active' => true,
                'metadata' => ['primary' => '#1E1E2E', 'accent' => '#CBA6F7'],
            ],
            [
                'name' => 'Tema Oceano',
                'type' => ShopItem::TYPE_THEME,
                'slug' => 'tema-oceano',
                'description' => 'Mergulhe no conhecimento com este tema azul profundo.',
                'gem_price' => 60,
                'active' => true,
                'metadata' => ['primary' => '#0F172A', 'accent' => '#38BDF8'],
            ],
            [
                'name' => 'Tema Floresta',
                'type' => ShopItem::TYPE_THEME,
                'slug' => 'tema-floresta',
                'description' => 'Explore o conhecimento com este tema verde vibrante.',
                'gem_price' => 60,
                'active' => true,
                'metadata' => ['primary' => '#14532D', 'accent' => '#86EFAC'],
            ],
            // Power-ups
            [
                'name' => 'Revisor Turbo',
                'type' => ShopItem::TYPE_POWER_UP,
                'slug' => 'revisor-turbo',
                'description' => 'Dobra os pontos de XP na próxima sessão de revisão.',
                'gem_price' => 25,
                'active' => true,
                'metadata' => ['effect' => 'xp_multiplier', 'value' => 2, 'duration' => 'one_session'],
            ],
            [
                'name' => 'Protetor de Sequência',
                'type' => ShopItem::TYPE_POWER_UP,
                'slug' => 'protetor-sequencia',
                'description' => 'Protege sua sequência diária por um dia.',
                'gem_price' => 20,
                'active' => true,
                'metadata' => ['effect' => 'streak_shield', 'duration' => '1_day'],
            ],
        ];

        foreach ($items as $item) {
            ShopItem::updateOrCreate(
                ['slug' => $item['slug']],
                $item
            );
        }

        Cache::forget('shop:active_items');
    }
}
